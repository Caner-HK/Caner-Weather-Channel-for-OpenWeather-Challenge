<?php
    require './access/api/config.php';
    require './access/fetch/curl_fetch.php';
    
    //IP
    class IPInfo {
        private static $details = null;
        public static function getIPDetails() {
            if (self::$details === null) {
                $IPinfoAPI = IPinfo_API;
                $ip = $_SERVER['REMOTE_ADDR'];
                $json = file_get_contents("https://ipinfo.io/{$ip}?token=$IPinfoAPI");
                self::$details = json_decode($json);
            }
            return self::$details;
        }
        public static function getLocationByIP() {
            $details = self::getIPDetails();
            return $details->city;
        }
        public static function getCountryByIP() {
            $details = self::getIPDetails();
            return $details->country;
        }
    }
    
    //GitHub open source source code has deleted the database part
    $db_user_ip = $_SERVER['REMOTE_ADDR'];
    $user_location = IPInfo::getCountryByIP() . ", " . IPInfo::getLocationByIP();
    $db_user_agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
    $db_visit_time = date('Y-m-d H:i:s');
    $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $connection = $_SERVER['HTTP_CONNECTION'] ?? '';
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
    $x_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $db_user_ip;
    $x_requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    
    //multi-language
    $default_lang = 'en-us';
    $supported_lang = ['zh-cn', 'zh-hk', 'zh-tw', 'en-us', 'uk-ua', 'hi'];
    $accept_language = strtolower($accept_language);
    $parsed_lang = [];
    
    if ($accept_language) {
        $languages = explode(',', $accept_language);
        foreach ($languages as $lang) {
            $parts = explode(';', $lang);
            $lang_code = trim($parts[0]);
            $qValue = 1.0;
            if (count($parts) > 1 && strpos($parts[1], 'q=') === 0) {
                $qValue = floatval(substr($parts[1], 2));
            }
            $parsed_lang[$lang_code] = $qValue;
        }
        arsort($parsed_lang);
    }
    
    $selected_lang = $default_lang;
    foreach ($parsed_lang as $code => $q) {
        if (in_array($code, $supported_lang)) {
            $selected_lang = $code;
            break;
        }
    }
    if (isset($_POST['Lang'])) {
        $lang_code = strtolower($_POST['Lang']);
        if (in_array($lang_code, $supported_lang)) {
            $selected_lang = $lang_code;
        }
        if (isset($_POST['setDefaultLang'])) {
            $expiration_date = time() + (86400 * 90);
            $cookie_value = json_encode(["Language" => $selected_lang, "Expiration" => date('Y-m-d H:i:s', $expiration_date)]);
            if (isset($_COOKIE['CWC-Profile'])) {
                $old_cookie_value = $_COOKIE['CWC-Profile'];
                $old_cookie_data = json_decode($old_cookie_value, true);
                $old_cookie_data["Language"] = $selected_lang;
                $cookie_value = json_encode($old_cookie_data);
            }
            setcookie('CWC-Profile', $cookie_value, $expiration_date, "/");
        }
    } else {
        if (isset($_COOKIE['CWC-Profile'])) {
            $cookie_value = $_COOKIE['CWC-Profile'];
            $profile_data = json_decode($cookie_value, true);
            if (isset($profile_data['Language']) && in_array($profile_data['Language'], $supported_lang)) {
                $selected_lang = $profile_data['Language'];
            }
        }
    }
    
    $lang = require "./access/language/main/{$selected_lang}.php";
    require "./access/functions/functions_{$selected_lang}.php";

    //Geo Coding
    function getCoordinates($address, $apiKey, $lang) {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey . "&language=" . $lang['lang'];
        $response = curlGet($url);
    
        $responseData = json_decode($response, true);
        if ($responseData['status'] == 'OK') {
            $latitude = $responseData['results'][0]['geometry']['location']['lat'];
            $longitude = $responseData['results'][0]['geometry']['location']['lng'];
            $formattedAddress = $responseData['results'][0]['formatted_address'];
            $GCountryCode = null;
    
            foreach ($responseData['results'][0]['address_components'] as $component) {
                if (in_array('country', $component['types'])) {
                    $GCountryCode = $component['short_name'];
                    break;
                }
            }
            return array($latitude, $longitude, $formattedAddress, $GCountryCode);
        } else {
            return false;
        }
    }
    
    $weatherData = "";
    $locationName = "";
    $Google_Key = Google_API;
    $GMapKey = GMap_Key;
    
    $countryCode = IPInfo::getCountryByIP();
    if (!isset($_GET['location'])) {
        $_GET['location'] = IPInfo::getLocationByIP();
    }
    
    $result = getCoordinates($_GET['location'], $Google_Key, $lang);
    if ($result !== false) {
        global $GCountryCode;
        $GCountryCode = $result[3];
    }
    
    //Units and Maps cookie logic
    if (isset($_COOKIE['CWC-Profile'])) {
        $cookieData = json_decode($_COOKIE['CWC-Profile'], true);
    } else {
        $cookieData = array();
    }
    $units = "metric";
    if (isset($_POST['Units'])) {
        $units = $_POST['Units'];
        if (isset($_POST['setDefaultUnits'])) {
            $cookieData["Units"] = $units;
            $cookieData["Expiration"] = date('Y-m-d H:i:s', time() + (86400 * 90));
        }
    } elseif (isset($cookieData['Units'])) {
        $units = $cookieData['Units'];
    }
    function getMapsChoice($maps) {
        if ($maps === "AutoMaps") {
            $country = IPInfo::getCountryByIP();
            return ($country === "CN") ? "AMap" : "GMaps";
        } else {
            return $maps;
        }
    }
    if (isset($_POST['Maps'])) {
        $mapsChoice = $_POST['Maps'];
        $cookieData['Maps'] = $mapsChoice;
        $cookieData["Expiration"] = date('Y-m-d H:i:s', time() + (86400 * 90));
        $maps = getMapsChoice($mapsChoice);
    } elseif (isset($cookieData['Maps'])) {
        $mapsChoice = $cookieData['Maps'];
        $maps = getMapsChoice($mapsChoice);
    } else {
        $mapsChoice = "GMaps";
        $maps = getMapsChoice($mapsChoice);
    }
    
    if (isset($_POST['setDefaultUnits']) || isset($_POST['Maps'])) {
        $cookieExpiration = time() + (86400 * 90);
        $jsonSettings = json_encode($cookieData);
        setcookie('CWC-Profile', $jsonSettings, $cookieExpiration, "/");
    }
    
    if ($result) {
        list($latitude, $longitude, $locationName, $GCountryCode) = $result;
    
        //Open Weather One Call API and AQI API
        $owmApiKey = OWM_API;
        $language = $lang['lang_variant'];
        $weatherUrl = "https://api.openweathermap.org/data/3.0/onecall?lat=$latitude&lon=$longitude&lang=$language&appid=$owmApiKey&units=$units";
        $airPollutionUrl = "http://api.openweathermap.org/data/2.5/air_pollution?lat=$latitude&lon=$longitude&appid=$owmApiKey";
        $airPollutionForecastUrl = "http://api.openweathermap.org/data/2.5/air_pollution/forecast?lat=$latitude&lon=$longitude&appid=$owmApiKey";
    
        $weatherDataOWM = fetchData($weatherUrl);
        $airPollutionDataOWM = fetchData($airPollutionUrl);
        $forecastAqiData = fetchData($airPollutionForecastUrl);
    
        if (isset($weatherDataOWM['current'])) {
            $current = $weatherDataOWM['current'];
            $timezone = $weatherDataOWM['timezone'];
            $timezone_offset = $weatherDataOWM['timezone_offset'];
        } else {
            if (isset($weatherDataOWM['cod']) && isset($weatherDataOWM['message'])) {
                $errorMessage = $lang['errmsg'] . ": <br>{$weatherDataOWM['cod']} - {$weatherDataOWM['message']}";
            } else {
                $errorMessage = $lang['errmsg_unknow'];
            }            
        }
    
        if ($airPollutionDataOWM !== null) {
            $aqi = $airPollutionDataOWM['list'][0]['main']['aqi'] ?? 'null';
            if ($aqi !== 'null') {
                $components = $airPollutionDataOWM['list'][0]['components'];
            }
        } else {
            $aqi = 'null';
        }
        if ($forecastAqiData === null) {
            $forecastAqiData = 'null';
        }
        if (isset($aqi) && $aqi !== 'null') {
            list($bgColor, $barColor) = getAqiStyle($aqi);
            $airPollutionSuggestion = getAirPollutionSuggestion($aqi);
            if (isset($airPollutionSuggestion)) {
                $displaySuggestion = nl2br($airPollutionSuggestion);
            } else {
                $displaySuggestion = $lang['errmsg_aqi'];
            }
        }
    }

    if ($selected_lang == "zh-hk" || $selected_lang == "zh-tw") {
        $misans_link_tag = '<link rel="stylesheet" href="https://resource.caner.hk/get/misans_tc/cwc_get.css">';
    } else {
        $misans_link_tag = '<link rel="stylesheet" href="https://resource.caner.hk/get/misans/cwc_get.css">';
    }
    
    //Google Analytics&accessibility&autocomplete
    $acc = false;
    $analytics = false;
    $autocomplete = "cwc";
    if (isset($_COOKIE['CWC-Profile'])) {
        $cookieData = json_decode($_COOKIE['CWC-Profile'], true);
        $acc = (isset($cookieData['Accessibility']) && $cookieData['Accessibility'] === "true") ? true : false;
        $analytics = (isset($cookieData['Google Analytics']) && $cookieData['Google Analytics'] === "true") ? true : false;
        $autocomplete = (isset($cookieData['Autocomplete']) ? $cookieData['Autocomplete'] : "cwc");
    }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang['lang']; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Caner Weather Channel - <?php echo sprintf($lang['site_title'], $locationName !== "" ? $locationName : $lang['global']); ?></title>
    <meta name="description" content="<?php echo $lang['site_description']; ?>">
    <?php echo $misans_link_tag; ?>
    <link rel="stylesheet" href="https://resource.caner.hk/get/toggle/get.css">
    <link rel="icon" href="https://resource.caner.hk/get/logo/cwc.png" type="image/x-icon">
    <?php if ($analytics): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-BMQH2HN7KX"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-BMQH2HN7KX');
    </script>
    <?php endif; ?>
    <style>
    body {
        padding-top: 58px;
        font-family: 'MiSans', sans-serif;
    }
    html {
        scroll-behavior: smooth;
    }
    html ,body {
        margin: 0;
    }
    ::selection {
        background-color: rgba(0, 0, 0, 0.8);
        color: #FFF;
    }
    nav {
        position: relative;
    }
    @media (min-width: 500px) {
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px; 
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }
    }
    #suggestions {
        position: absolute;
        border: 1px solid #000;
        list-style-type: none;
        padding: 0;
        margin-top: 5px;
        width: 250px;
        background-color: white;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 99;
        box-sizing: border-box;
        transition: all 0.3s ease;
        transform-origin: top;
        transform: scaleY(0);
        word-break: break-all;
    }
    #suggestions li {
        padding: 8px;
        cursor: pointer;
        transition: color 0.3s, background-color 0.3s ease, color 0.3s ease;
        border-bottom: 1px dashed #ccc;
        box-sizing: border-box;
    }
    #suggestions li:last-child {
        border-bottom: none;
    }
    #suggestions li:hover {
        background-color: #000;
        color: #FFF;
    }
    #suggestions .cwc-history-item {
        transition: color 0.3s, background-color 0.3s ease, color 0.3s ease;
    }
    #suggestions .cwc-history-item:hover {
        background-color: #000;
        color: #FFF;
    }
    #suggestions .cwc-history-item:hover .cwc-history-icon svg {
        fill: #FFF;
    }
    #suggestions .cwc-history-item .cwc-history-icon svg {
        fill: #000;
    }
    #suggestions .cwc-history-item.context-menu-active {
        transition: color 0.3s, background-color 0.3s ease, color 0.3s ease;
        background-color: #EF5350;
        color: #FFF;
    }
    #suggestions .cwc-history-item.context-menu-active:hover {
        background-color: #EF5350;
        color: #FFF;
    }
    #suggestions .cwc-history-item.context-menu-active .cwc-history-icon svg,
    #suggestions .cwc-history-item.context-menu-active:hover .cwc-history-icon svg {
        fill: #FFF;
    }
    #suggestions .cwc-history-item:hover {
        background-color: #000;
        color: #FFF;
    }
    #suggestions .cwc-history-item:hover .cwc-history-icon svg {
        fill: #FFF;
    }
    #minutely-forecast ul {
        list-style-type: none;
        padding-left: 0;
    }
    #minutely-forecast li {
        margin-bottom: 5px;
    }
    #minutely-forecast {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
    }
    #hourly-temperature-forecast {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
    }
    #hourly-weather-forecast-chart {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
    }
    #hourly-forecast {
        border: 1px solid #ccc;
        margin: 10px;
        padding: 10px;
    }
    #hourly-forecast .hour-item {
        border: 1px solid #ccc;
        padding: 10px;
        flex-shrink: 0;
        margin-right: 10px;
        width: 250px;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
    }
    #daily-forecast {
        border: 1px solid #ccc;
        margin: 10px;
        padding: 10px;
    }
    #daily-forecast .day-item {
        border: 1px solid #ccc;
        padding: 10px;
        flex-shrink: 0;
        margin-right: 10px;
        width: 250px;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
    }
    #weather-alerts {
        border-style: solid;
        border-color: #4CAF50;
        border-width: 0px 0px 0px 5px;
        padding: 5px 10px 8px 6px;
        margin: 10px;
        background-color: rgba(165, 214, 167, 0.7);
        word-wrap: break-word;
    }
    #weather-alerts-iosok {
        border-style: solid;
        border-color: #4CAF50;
        border-width: 0px 0px 0px 5px;
        padding: 5px 10px 8px 6px;
        margin: 10px;
        background-color: rgba(165, 214, 167, 0.7);
        word-wrap: break-word;
        transition: opacity 1s ease-out, height 1s ease-out;
            overflow: hidden; /* 防止内容在缩减高度时溢出 */
    }
    #backtopBtn {
        display: none;
    }
    #gameFrame {
        border: 1px solid #ccc;
        transition: opacity 0.5s ease, max-height 0.5s ease;
        overflow: hidden;
    }
    #gameButton {
        margin-left: 10px;
    }
    #ServiceError {
        color: #FF5252;
    }
    #changeTitle span, #changeChart span {
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }
    #button-text {
        transition: opacity 0.3s ease;
        opacity: 1;
    }
    header {
        background-color: #FAF9F8;
        padding-left: 10px;
        position: fixed;
        top: 0;
        width: 100%;
        height: 58px;
        box-shadow: none;
        z-index: 100;
        transition: box-shadow 0.3s ease;
    }
    footer {
        background-color: #FAF9F8;
    }
    a {
        text-decoration: none;
    }
    @keyframes blink-border {
        0%, 100% { 
            border-color: #EF5350; 
            background-color: rgba(239, 83, 80, 0.5);
        }
        50% { 
            border-color: transparent; 
            background-color: rgba(0, 0, 0, 0);
        }
    }
    @media screen and (max-width: 600px) {
        .hide-xs {
            display: none;
        }
    }
    @media screen and (max-width: 400px) {
        .hide-s {
            display: none;
        }
    }
    @media (max-width: 540px) {
    .cwc-mapcontrol {
        max-width: 70%;
    }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }
    .fade-text {
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    .fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    .fade-in-out {
        animation: fadeInOut 0.25s ease-in-out;
    }
    @keyframes rotateAnimation {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes scaleDown {
        from { transform: scale(1); }
        to { transform: scale(0); }
    }
    @keyframes scaleUp {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }
    #shareSvg {
        transform-origin: center;
        animation-duration: 0.25s;
        animation-fill-mode: forwards;
    }
    #shareTxt {
        animation-duration: 0.25s;
        animation-fill-mode: forwards;
    }
    .hidden {
        opacity: 0;
    }
    .header-btn {
        background-color: transparent;
        border: none;
        cursor: pointer;
        transition: transform 0.5s;
        transform-origin: 50% 43%;
    }
    .cwc-menu {
        width: 100%;
        height: 450px;
        background-color: #FAF9F8;
        position: relative;
        z-index: 99;
        margin-top: -449px;
        transition: margin-top 0.5s ease;
        overflow: hidden;
        border-bottom: 1px solid black;
    }
    .cwc-menu-content {
        padding-left: 10px;
        padding-right: 10px;
    }
    .cwc-icon-btn, .cwc-input {
        box-sizing: border-box;
        height: 42px; 
        border: 1px solid #000;
        background-color: transparent;
        border-radius: 0px !important;
    }
    .cwc-icon-btn {
        width: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 5px;
        transition: color 0.3s, background-color 0.3s, border-color 0.3s;
    }
    .cwc-icon-btn svg {
        fill: #000;
        transition: fill 0.3s;
    }
    .cwc-icon-btn:hover {
        background-color: #000;
    }
    .cwc-icon-btn:hover svg {
        fill: white;
    }
    .cwc-icon-btn svg polyline,
    .cwc-icon-btn svg line {
        stroke: #000;
    }
    .cwc-icon-btn:hover svg polyline,
    .cwc-icon-btn:hover svg line {
        stroke: white;
    }
    .cwc-margin-10 {
        width: calc(100% - 20px);
        margin-left: 10px;
        margin-right: 10px;
    }
    .cwc-btn-group {
        position: absolute;
        bottom: 10px;
        display: flex;
        justify-content: space-between;
    }
    .cwc-input {
        width: 250px;
        padding-left: 10px;
        padding-right: 35px;
        padding-top: 1px;
        font-size: 14px;
    }
    .cwc-input:focus {
        outline: none;
    }
    .cwc-alert-tip {
        margin-left: 30px;
    }
    .cwc-weather-suggestion {
        font-size: 15px;
    }
    .gpt-suggestion-tips {
        display: block;
        height: 70px;
    }
    .cwc-selection {
        border: 1px solid black;
        background-color: transparent;
        color: black;
        height: 42px;
        width: 250px;
        padding: 14px;
        box-sizing: border-box;
        position: relative;
        cursor: pointer;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .selection-value {
        font-size: 14px;
        color: #757575;
        line-height: 14px;
    }
    .cwc-options {
        display: none;
        position: absolute;
        left: -0.7px;
        top: 42px;
        width: calc(100% + 2px);
        background-color: white;
        border: 1px solid black;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.5);
        box-sizing: border-box;
        transition: all 0.3s ease;
        transform-origin: top;
        transform: scaleY(0);
        z-index: 99;
    }
    .cwc-option {
        padding: 8px;
        font-size: 15px;
        border-bottom: 1px dashed #ccc;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .cwc-option:last-child {
        border-bottom: none;
    }
    .cwc-option:hover {
        background-color: black; 
        color: white;
    }
    .cwc-checkbox {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        top: 2px;
        left: -4px;
        border: 1px solid black;
        border-radius: 0px !important;
        cursor: pointer;
        display: inline-block;
        position: relative;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 80%;
        transition: background-color 0.2s, background-image 0.2s;
        z-index: 98;
    }
    .cwc-checkbox-label {
        position: relative;
        font-size: 15px;
        top: 0px;
    }
    .cwc-checkbox:checked {
        background-color: black;
        border-color: black;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23FFFFFF" viewBox="0 0 24 24"><path d="M 20.292969 5.2929688 L 9 16.585938 L 4.7070312 12.292969 L 3.2929688 13.707031 L 9 19.414062 L 21.707031 6.7070312 L 20.292969 5.2929688 z"/></svg>');
    }
    .cwc-game {
        margin: 10px;
        width: calc(100% - 22px);
        opacity: 0;
        max-height: 0;
    }
    .cwc-cookies {
        padding: 10px 10px 10px 10px;
        background-color: #FAF9F8;
        margin-top: calc(-100% + 16px);
        display: none;
        overflow: hidden;
        transition: margin-top 0.5s ease;
        border-bottom: 1px solid black;
    }
    .cookies-title {
        font-size: 23px;
        font-weight: bold;
    }
    .cookies-content {
        font-size: 14px;
    }
    .cwc-result-ok-container {
        border-style: solid;
        border-color: #4CAF50;
        border-width: 0px 0px 0px 5px;
        padding: 2px 10px 2px 6px;
        margin: 10px;
        background-color: rgba(165, 214, 167, 0.7);
        word-wrap: break-word;
    }
    .cwc-result-err-container {
        border-style: solid;
        border-color: #EF5350;
        border-width: 0px 0px 0px 5px;
        padding: 2px 10px 2px 6px;
        margin: 10px;
        background-color: rgba(239, 83, 80, 0.7);
        word-wrap: break-word;
        animation: blink-border 1.5s linear infinite;
    }
    .cwc-btn {
        border-radius: 0px !important;
        border: 1px solid black;
        height: 36px;
        width: 297px;
        font-size: 14px;
        transition: color 0.3s, background-color 0.3s, border-color 0.3s;
        background-color: transparent;
        color: black;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        margin-bottom: 5px;
    }
    .cwc-btn:hover {
        background-color: black;
        color: white;
    }
    .cwc-action-btn {
        margin-left: 0px;
        margin-bottom: 0px;
        margin-top: 10px;
    }
    .cwc-link {
        color: #1976D2;
        border-bottom: 1px solid #1976D2;
        text-decoration: none; 
        transition: border-bottom 0.3s ease;
    }
    .cwc-link:visited {
        color: #1976D2;
    }
    .cwc-link:after {
        content: '';
        display: inline-block;
        width: 18px;
        height: 18px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%231976D2"/></svg>');
        background-size: contain;
        margin-left: -2px;
        margin-top: -2px;
        vertical-align: middle;
        white-space: nowrap; 
        transition: transform 0.3s ease;
    }
    .cwc-footer-link {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
        white-space: nowrap;
        transition: border-bottom 0.3s ease;
    }
    .cwc-footer-link:visited {
        color: black;
    }
    .cwc-footer-link:after {
        content: '';
        display: inline-block;
        width: 18px;
        height: 18px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%23000000"/></svg>');
        background-size: contain;
        margin-left: -2px;
        margin-top: -2px;
        vertical-align: middle;
        white-space: nowrap; 
        transition: transform 0.3s ease;
    }
    .cwc-footer-link-noafter {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
        transition: border-bottom 0.3s ease;
    }
    .cwc-footer-link-noafter:visited {
        color: black;
    }
    .cwc-footer-top {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
    }
    .cwc-footer-top:visited {
        color: black;
    }
    .cwc-footer-top:hover,
    .cwc-footer-link:hover,
    .cwc-link:hover,
    .cwc-footer-link-noafter:hover {
        border-bottom: 2px solid;
    }
    .cwc-link:hover:after,
    .cwc-footer-link:hover:after {
        transform: rotate(45deg);
    }
    .link-space {
        margin-right: 15px;
    }
    .cookies-title-fade-out {
        animation: fadeOut 0.5s forwards;
    }
    .cookies-title-fade-in {
        animation: fadeIn 0.5s forwards;
    }
    .cwc-flex-center-container {
        display: flex;
        align-items: center;
    }
    .cwc-flexbox-container {
        display: flex;
        justify-content: space-between;
    }
    .cwc-column {
        flex: 1;
    }
    .cwc-column-aqi {
        flex-basis: 0%;
    }
    .aqi-content {
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .cwc-switch-container {
        height: 160px;
        margin-top: 15px;
        overflow-y: auto;
    }
    .tide-content {
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #tideTitle, #btnText {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .visible {
        opacity: 1;
    }
    .hidden {
        display: none;
    }
    .initial-state {
        display: block;
        opacity: 0;
    }
    .scrollable-container-hourly {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
    }
    .scrollable-container-daily {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
    }
    .alert-active {
        border-color: #EF5350;
        animation: blink-border 1.5s linear infinite;
        background-color: rgba(239, 83, 80, 0.5);
    }
    .shadow {
        box-shadow: 2px 4px 2px -2px gray;
    }
    .footer {
        padding: 10px;
        border-top: 1px solid #2a2a2a;
    }
    .footer-border {
        border-color: #2a2a2a !important;
    }
    .cwc-flex {
        display: flex;
    }
    .cwc-svg-right {
        float: right !important;
    }
    .fade-out {
        animation: fadeOut 0.25s;
        opacity: 0;
    }
    .fade-in {
        animation: fadeIn 0.25s;
        animation-fill-mode: forwards;
    }
    .fade-out-gpt {
        opacity: 0;
        transition: opacity 0.3s ease-out;
    }
    .fade-in-gpt {
        opacity: 1;
        transition: opacity 0.3s ease-in;
    }
    .cwc-progress-container {
        display: flex;
        align-items: center;
        width: 100%;
        margin-top: -8px;
    }
    .cwc-progress {
        position: relative;
        flex-grow: 1;
        height: 10px;
        background-color: #E3F2FD;
        border-radius: 0px;
        margin: 0 5px;
        border-radius: 5px;
    }
    .cwc-progress-determinate {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        background-color: #2196F3;
        border-radius: 5px;
    }
    .cwc-progress-text {
        font-size: 10px;
        position: relative;
        top: -1px;
    }
    .cwc-progress-container {
        position: relative;
    }   
    .progress-arrow {
        position: absolute;
        bottom: -18px;
        left: 0%;
        transform: translateX(-50%);
    }
    .arrow-text {
        position: absolute;
        top: 12px;
        font-size: 9px;
        white-space: nowrap;
    }
    .cwc-wraptextr {
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    .cwc-map {
        border: 1px solid #ccc;
        min-height: 250px;
        height: 32vh;
        margin: 10px;
        transition: height 0.5s ease;
        z-index: 1;
    }
    .cwc-maps-label-icon {
        position: absolute;
        height: 60px;
        top: 15px;
        right: 3px;
    }
    .cwc-center {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
        height: 27vh;
        flex-direction: column;
    }
    .cwc-center h3 {
        flex: 0 1 auto;
        text-align: center;
        white-space: normal;
    }
    .cwc-close-btn {
        border: none;
        background: none;
        cursor: pointer;
        padding: 0;
        margin-left: -82px;
        height: 24px;
        display: none;
    }
    .shake {
        animation: shake 0.5s;
    }
    .cwc-maps-label {
        color: #000;
    }
    .cwc-maps-content {
        display: block;
        width: 150px;
    }
    .cwc-maps-link {
        text-decoration: none;
        color: #2196F3;
    }
    .cwc-maps-span {
        color: #2196F3;
        margin-top: 2px;
    }
    .cwc-menu-btn {
        position: fixed;
        right: 10px;
        top: 14px;
    }
    .cwc-title {
        position: relative;
        top: -4px;
    }
    .cwc-from {
         margin-top: 5px; 
    }
    .cwc-from-container {
        margin: 15px 10px 10px 10px;
    }
    .cwc-headline {
        font-size: 52px;
    }
    .cwc-subhead {
         font-size: 18px;
    }
    .cwc-headtip-n {
        float: right;
        position: relative;
        top: 14px;
        right: 48px;
        font-size: 10px;
    }
    .cwc-text-small {
        font-size: 14px;
    }
    .cwc-text-normal {
        font-size: 16px;
    }
    .cwc-text-small-2 {
        font-size: 15px;
    }
    .cwc-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 30px;
        margin-bottom: 30px;
    }
    .cwc-special-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .cwc-special-2-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 30px;
        margin-bottom: 28px;
    }
    .cwc-errorpage-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 15px;
        margin-bottom: 15px;
    }
    .cwc-errorpage-top-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 10px;
        margin-bottom: 15px;
    }
    .cwc-top-20 {
        margin-top: 20px;
    }
    .cwc-hr-dashed {
        border: 0;
        border-top: 1px dashed #ccc;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .cwc-hr-double {
        border: 0;
        border-top: 3px double #ccc;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .cwc-hr-solid {
        border: 0;
        border-top: 1px solid #ccc;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .cwc-head-hourly {
        font-size: 40px;
    }
    .cwc-head-daily {
        font-size: 40px;
    }
    .cwc-daily-arrow {
        font-size: 28px;
        font-weight: 900;
    }
    .cwc-summary {
        font-size: 19px;
    }
    .cwc-summary-container {
        margin-top: 20px;
        margin-bottom: 20px;
        height: 110px;
        justify-content: center;
        display: flex;
        align-items: center;
        border-top: 1px dashed #ccc;
        border-bottom: 1px dashed #ccc;
    }
    .cwc-vane-container {
        position: relative;
        display: inline-block;
        margin-top: 18px;
        margin-left: 10px;
        margin-bottom: 20px;
    }
    .cwc-weather-container {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
        word-wrap: break-word;
    }
    .cwc-weather-result {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
    }
    .cwc-gpt {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 10px;
        word-wrap: break-word;
    }
    .cwc-weather-suggestion {
        transition: opacity 0.3s ease-in-out;
        opacity: 1;
    }
    .scrollable-container {
        height: 250px;
        overflow-y: auto;
    }
    .wind-direction {
        position: relative;
        display: inline-block;
    }
    .north-indicator {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
    }
    .cwc-menu-label {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .cwc-menu-interval {
        margin-top: 10px;
    }
    .cwc-horizontal-container {
        display: flex;
        align-items: center;
    }
    .cwc-maps-label, .cwc-maps-span, .cwc-maps-link {
        font-family: 'misans', sans-serif;
        font-size: 12px;
    }
    .cwc-x {
        position: relative;
        bottom: 2px;
    }
    .cwc-typo {
        margin-top: 20px;
    }
    .cwc-typo blockquote {
        margin:1em 0;
        padding-left:1em;
        font-weight:400;
        border-left:4px solid #ccc;
    }
    .cwc-typo blockquote:last-child {
        margin-bottom:0
    }
    .cwc-typo blockquote footer {
        color:rgba(0,0,0,.54);
        font-size:86%
    }
    .cwc-break-word {
      overflow-wrap: break-word;
    }
    .footer-head {
        font-size: 25px;
    }
    .error-page-btn {
        margin-top: 8px;
        margin-bottom: 25px;
        margin-left: 10px;
    }
    .follow-icon {
      font-size: 36px;
      margin: 0px 4px 0px 4px;
    }
    .cwc-svg path,
    .cwc-svg rect {
        fill: #2a2a2a !important;
    }
    .c-w-c {
        color: #2a2a2a !important;
    }
    .cwc-weather-icon-container {
        position: relative;
        margin-left: 45px;
    }
    .cwc-weather-icon-svg {
        position: absolute;
        left: 22px;
    }
    .cwc-svg-pin {
        width: 25px;
        height: 25px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNXB4JyBoZWlnaHQ9JzI1cHgnIGZpbGw9JyMyYTJhMmEnIHZpZXdCb3g9JzAgMCA1MTIgNTEyJz48cGF0aCBkPSdNMjU2IDQ4QTIwOC4yMyAyMDguMjMgMCAwMDQ4IDI1NmMwIDExNC42OCA5My4zMSAyMDggMjA4IDIwOGEyMDguMjMgMjA4LjIzIDAgMDAyMDgtMjA4YzAtMTE0LjY5LTkzLjMxLTIwOC0yMDgtMjA4em0tOCAzNjFWMjY0SDEwM2wyNTktMTE0LjExeicvPjwvc3ZnPiA=");
        position: absolute;
        margin-top: 20px;
    }
    .cwc-svg-done {
        width: 25px;
        height: 25px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyMyYTJhMmEnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFptNDguMTksMTIxLjQyLDI0LjEsMjEuMDYtNzMuNjEsODQuMS0yNC4xLTIzLjA2Wk0xOTEuOTMsMzQyLjYzLDEyMS4zNywyNzIsMTQ0LDI0OS4zNywyMTQuNTcsMzIwWm02NSwuNzlMMTg1LjU1LDI3MmwyMi42NC0yMi42Miw0Ny4xNiw0Ny4yMUwzNjYuNDgsMTY5LjQybDI0LjEsMjEuMDZaJy8+PC9zdmc+IA==");
        position: absolute;
        margin-top: 20px;
    }
    .cwc-svg-checked {
        width: 25px;
        height: 25px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyMyYTJhMmEnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFpNMjE4LDM2MC4zOCwxMzcuNCwyNzAuODFsMjMuNzktMjEuNDEsNTYsNjIuMjJMMzUwLDE1My40NiwzNzQuNTQsMTc0WicvPjwvc3ZnPiA=");
        position: absolute;
        margin-top: 20px;
    }
    .cwc-svg-error {
        width: 25px;
        height: 25px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyMyYTJhMmEnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFptODYuNjMsMjcyTDMyMCwzNDIuNjNsLTY0LTY0LTY0LDY0TDE2OS4zNywzMjBsNjQtNjQtNjQtNjRMMTkyLDE2OS4zN2w2NCw2NCw2NC02NEwzNDIuNjMsMTkybC02NCw2NFonLz48L3N2Zz4g");
        position: absolute;
        margin-top: 20px;
    }
    .cwc-svg-alert {
        width: 25px;
        height: 25px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwb2x5Z29uIGZpbGw9JyMyYTJhMmEnIHBvaW50cz0nMjQwIDMwNCAyNzIgMzA0IDI3OCAxNDQgMjM0IDE0NCAyNDAgMzA0JyBzdHlsZT0nZmlsbDpub25lJy8+PHBhdGggZmlsbD0nIzJhMmEyYScgZD0nTTI1Niw0OEMxNDEuMzEsNDgsNDgsMTQxLjMxLDQ4LDI1NnM5My4zMSwyMDgsMjA4LDIwOCwyMDgtOTMuMzEsMjA4LTIwOFMzNzAuNjksNDgsMjU2LDQ4Wm0yMCwzMTkuOTFIMjM2di00MGg0MFpNMjcyLDMwNEgyNDBsLTYtMTYwaDQ0WicvPjwvc3ZnPiA=");
        position: absolute;
        margin-top: 20px;
    }
    .cwc-svg-uvi {
        width: 35px;
        height: 35px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none"><path d="M12 3V4M12 20V21M4 12H3M6.31412 6.31412L5.5 5.5M17.6859 6.31412L18.5 5.5M6.31412 17.69L5.5 18.5001M17.6859 17.69L18.5 18.5001M21 12H20M16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12Z" stroke="%23FFC008" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        display: inline-block;
    }
    .cwc-svg-visibility {
        width: 35px;
        height: 35px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23009688' width='35' height='35' viewBox='0 0 24 24'%3E%3Cpath d='M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z'/%3E%3C/svg%3E");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        display: inline-block;
    }
    .cwc-svg-humidity {
        width: 35px;
        height: 35px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB3aWR0aD0nMzVweCcgaGVpZ2h0PSczNXB4JyB2aWV3Qm94PScwIDAgMjQgMjQnIGZpbGw9J25vbmUnIHhtbG5zPSdodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2Zyc+PHBhdGggZD0nTTE1LjAwNjYgMy4yNTYwOEMxNi44NDgzIDIuODU3MzcgMTkuMTMzMSAyLjg3NzMgMjIuMjQyMyAzLjY1MjY4QzIyLjc3ODEgMy43ODYyOSAyMy4xMDM4IDQuMzI3OTEgMjIuOTY5OSA0Ljg2MjQxQzIyLjgzNiA1LjM5NjkxIDIyLjI5MzEgNS43MjE5IDIxLjc1NzMgNS41ODgyOUMxOC44NjY2IDQuODY3NDIgMTYuOTAxNSA0Ljg4NzQ3IDE1LjQzMDggNS4yMDU4N0MxMy45NTU1IDUuNTI1MjQgMTIuODk1IDYuMTU4NjcgMTEuNzcxNSA2Ljg0MzYzTDExLjY4NzQgNi44OTQ5NEMxMC42MDQ0IDcuNTU1NjUgOS40MDUxNSA4LjI4NzI5IDcuODIwNzMgOC41NTA2OUM2LjE3NzM0IDguODIzODggNC4yMzYwMiA4LjU4MjM1IDEuNjI4ODMgNy41NDE4N0MxLjExNjA3IDcuMzM3MjQgMC44NjY2NzQgNi43NTY2NyAxLjA3MTggNi4yNDUxM0MxLjI3NjkyIDUuNzMzNTkgMS44NTg4OSA1LjQ4NDc5IDIuMzcxNjUgNS42ODk0M0M0Ljc2NDM1IDYuNjQ0MyA2LjMyMjk1IDYuNzc2OTkgNy40OTIgNi41ODI2NUM4LjY3ODg4IDYuMzg1MzUgOS41ODM3MyA1LjgzOTE2IDEwLjcyODYgNS4xNDExOUMxMS44NTUgNC40NTQ0NSAxMy4xNjk0IDMuNjUzOCAxNS4wMDY2IDMuMjU2MDhaJyBmaWxsPScjMDBCQ0Q0Jy8+PHBhdGggZD0nTTIyLjI0MjMgNy42NDMwMkMxOS4xMzMxIDYuODY3NjUgMTYuODQ4MyA2Ljg0NzcyIDE1LjAwNjYgNy4yNDY0MkMxMy4xNjk0IDcuNjQ0MTUgMTEuODU1IDguNDQ0NzkgMTAuNzI4NiA5LjEzMTUzQzkuNTgzNzMgOS44Mjk1IDguNjc4ODggMTAuMzc1NyA3LjQ5MiAxMC41NzNDNi4zMjI5NSAxMC43NjczIDQuNzY0MzUgMTAuNjM0NiAyLjM3MTY1IDkuNjc5NzdDMS44NTg4OSA5LjQ3NTE0IDEuMjc2OTIgOS43MjM5MyAxLjA3MTggMTAuMjM1NUMwLjg2NjY3NCAxMC43NDcgMS4xMTYwNyAxMS4zMjc2IDEuNjI4ODMgMTEuNTMyMkM0LjIzNjAyIDEyLjU3MjcgNi4xNzczNCAxMi44MTQyIDcuODIwNzMgMTIuNTQxQzkuNDA1MTUgMTIuMjc3NiAxMC42MDQ0IDExLjU0NiAxMS42ODc0IDEwLjg4NTNMMTEuNzcxNSAxMC44MzRDMTIuODk1IDEwLjE0OSAxMy45NTU1IDkuNTE1NTggMTUuNDMwOCA5LjE5NjIxQzE2LjkwMTUgOC44Nzc4MSAxOC44NjY2IDguODU3NzcgMjEuNzU3MyA5LjU3ODYzQzIyLjI5MzEgOS43MTIyNCAyMi44MzYgOS4zODcyNiAyMi45Njk5IDguODUyNzVDMjMuMTAzOCA4LjMxODI1IDIyLjc3ODEgNy43NzY2MyAyMi4yNDIzIDcuNjQzMDJaJyBmaWxsPScjMDBCQ0Q0Jy8+PHBhdGggZmlsbC1ydWxlPSdldmVub2RkJyBjbGlwLXJ1bGU9J2V2ZW5vZGQnIGQ9J00xOC45OTk4IDEwLjAyNjZDMTguNjUyNiAxMC4wMjY2IDE4LjM2MzMgMTAuMjA1OSAxOC4xNjE0IDEwLjQ3NzJDMTguMDkwNSAxMC41NzMgMTcuOTI2NiAxMC43OTcyIDE3LjcwODkgMTEuMTExQzE3LjQxOTMgMTEuNTI4MyAxNy4wMzE3IDEyLjEwODIgMTYuNjQyNCAxMi43NTU1QzE2LjI1NSAxMy4zOTk2IDE1Ljg1NTMgMTQuMTI4IDE1LjU0OTUgMTQuODM5N0MxNS4yNTY3IDE1LjUyMTMgMTQuOTk4OSAxNi4yNjE0IDE0Ljk5OTkgMTcuMDExN0MxNS4wMDA2IDE3LjIyMjMgMTUuMDI1OCAxNy40MzM5IDE1LjA2MDQgMTcuNjQxMkMxNS4xMTgyIDE3Ljk4NzIgMTUuMjM1NiAxOC40NjM2IDE1LjQ4MDQgMTguOTUyMUMxNS43MjcyIDE5LjQ0NDYgMTYuMTEzMSAxOS45Njc0IDE2LjcxMDcgMjAuMzY0OEMxNy4zMTQ2IDIwLjc2NjQgMTguMDc0OCAyMSAxOC45OTk4IDIxQzE5LjkyNDggMjEgMjAuNjg1IDIwLjc2NjQgMjEuMjg4OCAyMC4zNjQ4QzIxLjg4NjQgMTkuOTY3NCAyMi4yNzI0IDE5LjQ0NDYgMjIuNTE5MiAxOC45NTIyQzIyLjc2NCAxOC40NjM2IDIyLjg4MTUgMTcuOTg3MiAyMi45MzkzIDE3LjY0MTNDMjIuOTc0IDE3LjQzMzcgMjIuOTk5NSAxNy4yMjE1IDIyLjk5OTggMTcuMDEwN0MyMy4wMDAxIDE2LjI2MDQgMjIuNzQzIDE1LjUyMTQgMjIuNDUwMSAxNC44Mzk3QzIyLjE0NDQgMTQuMTI4IDIxLjc0NDcgMTMuMzk5NiAyMS4zNTczIDEyLjc1NTVDMjAuOTY4IDEyLjEwODIgMjAuNTgwMyAxMS41MjgzIDIwLjI5MDcgMTEuMTExQzIwLjA3MyAxMC43OTcyIDE5LjkwOSAxMC41NzMgMTkuODM4MiAxMC40NzcyQzE5LjYzNjMgMTAuMjA1OSAxOS4zNDY5IDEwLjAyNjYgMTguOTk5OCAxMC4wMjY2Wk0yMC42MTE5IDE1LjYyNTdDMjAuMzU1MiAxNS4wMjgxIDIwLjAwNDkgMTQuMzg0OCAxOS42NDIzIDEzLjc4MkMxOS40MjE4IDEzLjQxNTQgMTkuMjAwNyAxMy4wNzAyIDE4Ljk5OTggMTIuNzY3NEMxOC43OTg5IDEzLjA3MDIgMTguNTc3OCAxMy40MTU0IDE4LjM1NzMgMTMuNzgyQzE3Ljk5NDggMTQuMzg0OCAxNy42NDQ1IDE1LjAyODEgMTcuMzg3OCAxNS42MjU3TDE3LjM3MzIgMTUuNjU5NUMxNy4xOTY1IDE2LjA3MDQgMTYuOTg3NyAxNi41NTYyIDE3LjAwMDEgMTcuMDEwMUMxNy4wMTIxIDE3LjM2OTEgMTcuMTA4OCAxNy43Mzk3IDE3LjI2OTMgMTguMDU5OUMxNy4zOTc0IDE4LjMxNTcgMTcuNTc0IDE4LjU0MTEgMTcuODIwMSAxOC43MDQ4QzE4LjA2IDE4Ljg2NDMgMTguNDI0OCAxOS4wMDQ4IDE4Ljk5OTggMTkuMDA0OEMxOS41NzQ4IDE5LjAwNDggMTkuOTM5NiAxOC44NjQzIDIwLjE3OTUgMTguNzA0OEMyMC40MjU2IDE4LjU0MTEgMjAuNjAyMiAxOC4zMTU2IDIwLjczMDQgMTguMDU5OUMyMC44OTA5IDE3LjczOTcgMjAuOTg3NiAxNy4zNjkxIDIwLjk5OTYgMTcuMDFDMjEuMDEyMSAxNi41NTYzIDIwLjgwMzIgMTYuMDcwNSAyMC42MjY1IDE1LjY1OTdMMjAuNjExOSAxNS42MjU3WicgZmlsbD0nIzAwQkNENCcvPjxwYXRoIGQ9J00xNC4xMjk2IDExLjUzMDhDMTQuODg5OSAxMS4yODQ3IDE1LjQ3MjggMTIuMDc2IDE1LjExNTMgMTIuNzg5MkMxNC45NTIgMTMuMTE1MSAxNC43NjgzIDEzLjM5MjQgMTQuNDAzMSAxMy41MjE0QzEzLjQyNiAxMy44NjY2IDEyLjYxNjYgMTQuMzUyNyAxMS43NzE1IDE0Ljg2NzlMMTEuNjg3NCAxNC45MTkyQzEwLjYwNDQgMTUuNTc5OSA5LjQwNTE2IDE2LjMxMTUgNy44MjA3NCAxNi41NzQ5QzYuMTc3MzUgMTYuODQ4MSA0LjIzNjA0IDE2LjYwNjYgMS42Mjg4NCAxNS41NjYxQzEuMTE2MDggMTUuMzYxNSAwLjg2NjY4OCAxNC43ODA5IDEuMDcxODEgMTQuMjY5NEMxLjI3Njk0IDEzLjc1NzggMS44NTg5IDEzLjUwOSAyLjM3MTY3IDEzLjcxMzdDNC43NjQzNiAxNC42Njg1IDYuMzIyOTcgMTQuODAxMiA3LjQ5MjAxIDE0LjYwNjlDOC42Nzg4OSAxNC40MDk2IDkuNTgzNzQgMTMuODYzNCAxMC43Mjg2IDEzLjE2NTRDMTEuODE2NiAxMi41MDIxIDEyLjkzNjMgMTEuOTE3MSAxNC4xMjk2IDExLjUzMDhaJyBmaWxsPScjMDBCQ0Q0Jy8+PC9zdmc+");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        display: inline-block;
    }
    .cwc-svg-clouds {
        width: 35px;
        height: 35px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB3aWR0aD0nMzVweCcgaGVpZ2h0PSczNXB4JyB2aWV3Qm94PScwIDAgMjQgMjQnIGZpbGw9J25vbmUnIHhtbG5zPSdodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2Zyc+PHBhdGggZmlsbC1ydWxlPSdldmVub2RkJyBjbGlwLXJ1bGU9J2V2ZW5vZGQnIGQ9J00yLjM0Mjk3IDEzLjc5MjZDMS4zNTUwNSAxMi43ODIzIDAuOTA1Njc4IDExLjI5ODcgMS4wMTc1IDkuOTgxOTZDMS4xMTc1NSA4LjgwMzcxIDEuNjU0MDkgNy42MTQwMyAyLjczODc5IDYuODM0NjZDMy42ODM3IDYuMTU1NzMgNC45MzI3MiA1Ljg2NzQ0IDYuNDI4NjQgNi4wNDc1NEM3LjA3ODEgNC43NTgyOCA3Ljk0MzM5IDMuODc0OTkgOC45NTEwNSAzLjM5OTUyQzEwLjEyNTIgMi44NDU0OSAxMS4zNjY0IDIuOTA2MDkgMTIuNDI0OCAzLjM0NTc2QzEzLjkzMzMgMy45NzI0NCAxNS4xODk5IDUuNDM4NTkgMTUuNDM1MyA3LjE4MDIyQzE1LjY1OTIgNy4yMzg4MyAxNS44Nzg1IDcuMzEwOTEgMTYuMDkyIDcuMzk1MjRDMTguMjcyMSA4LjI1NjM0IDIwLjA2MjcgMTAuNTI4OCAxOS43NzczIDEzLjA5NTlDMjEuODAxOSAxMy41ODYgMjIuOTY2MiAxNS4yNzcgMjIuOTk5MyAxNy4wMDkxQzIzLjAxODMgMTguMDA3IDIyLjY2MDMgMTkuMDIzNCAyMS44Nzg3IDE5Ljc4ODhDMjEuMDkxNiAyMC41NTk3IDE5Ljk1NDkgMjEgMTguNTUyNiAyMUw3LjE3NzA2IDIxQzMuNjE5MzQgMjEgMS43NzYwMiAxNy44ODQ5IDIuMDIyNiAxNS4xODIxQzIuMDY1NTkgMTQuNzEwOSAyLjE3MTA5IDE0LjI0MTYgMi4zNDI5NyAxMy43OTI2Wk0zLjQ5MjYzIDEyLjA1MTFDMy42ODM3MiAxMS44Njc5IDMuODk0MTIgMTEuNjk3MSA0LjEyNDM0IDExLjU0MTRDNS4yOTE4OSAxMC43NTE3IDYuODU5MjcgMTAuNDIyMSA4Ljc3ODQyIDEwLjY4MDZDOS41OTUwOSA5LjA3NjY4IDEwLjY4NzYgOC4wMTE1OSAxMS45MzM1IDcuNDUzNDRDMTIuNDAyMyA3LjI0MzQxIDEyLjg4IDcuMTExMDggMTMuMzU1NyA3LjA0Njc0QzEzLjEwMzUgNi4yMzEzMSAxMi40NjgxIDUuNTI5NDQgMTEuNjU3NSA1LjE5Mjc0QzExLjA1MjQgNC45NDEzNCAxMC40MDQ4IDQuOTI1IDkuODA0NTIgNS4yMDgyN0M5LjIwMDU4IDUuNDkzMjQgOC41MTg4MSA2LjE0MjE1IDcuOTgzNTggNy40NTgwMkM3Ljc4MTk1IDcuOTUzNzIgNy4yNTIwNCA4LjIzMTIgNi43MzQ0NCA4LjEyMzM5QzUuMzEyNSA3LjgyNzIyIDQuNDI3NDMgOC4wODQwOCAzLjkwNTgxIDguNDU4ODdDMy4zNzc0NCA4LjgzODUxIDMuMDcwMTIgOS40NDcgMy4wMTAzMiAxMC4xNTEyQzIuOTUwMDMgMTAuODYxMiAzLjEzMDc1IDExLjUzNjQgMy40OTI2MyAxMi4wNTExWk0xMC4zMTc1IDEyLjEwOTVDMTEuMDA2MiAxMC40OTYzIDExLjkwMzQgOS42NTg0NCAxMi43NTExIDkuMjc4NjdDMTMuNjAxNSA4Ljg5Nzc1IDE0LjUxODkgOC45MjQyNCAxNS4zNTczIDkuMjU1MzlDMTcuMTExNyA5Ljk0ODM4IDE4LjIxNTQgMTEuNzkxOCAxNy42NTA4IDEzLjUwNDFDMTcuNDE3OCAxNC4yMTA3IDE3LjkzNjMgMTQuOTMzNCAxOC42NjcyIDE0Ljk1NjNDMjAuMjM5MiAxNS4wMDU1IDIwLjk4MSAxNi4wNzA4IDIwLjk5OTYgMTcuMDQ3M0MyMS4wMDkxIDE3LjU0MzYgMjAuODMzNCAxOC4wMTMxIDIwLjQ3OTMgMTguMzZDMjAuMTMwNiAxOC43MDE1IDE5LjUyOTYgMTkgMTguNTUyNiAxOUw3LjE3NzA2IDE5QzUuMDY2MiAxOSAzLjg0ODQgMTcuMTgyNiA0LjAxNDMzIDE1LjM2MzhDNC4wOTQwOSAxNC40ODk1IDQuNTAxMDIgMTMuNzAxMSA1LjI0NDg3IDEzLjE5OEM1Ljk5MDA4IDEyLjY5NCA3LjIxMTU2IDEyLjM4NDcgOS4wODQ5IDEyLjc1ODlDOS41OTI4OSAxMi44NjA0IDEwLjExMTYgMTIuNTkxOCAxMC4zMTc1IDEyLjEwOTVaJyBmaWxsPScjNTI1MjUyJy8+PC9zdmc+");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        display: inline-block;
    }
    .cwc-svg-pressure {
        width: 35px;
        height: 35px;
        background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB3aWR0aD0nMzVweCcgaGVpZ2h0PSczNXB4JyB2aWV3Qm94PScwIDAgMjQgMjQnIGZpbGw9J25vbmUnIHhtbG5zPSdodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2Zyc+PGcgaWQ9J1NWR1JlcG9fYmdDYXJyaWVyJyBzdHJva2Utd2lkdGg9JzAnLz48ZyBpZD0nU1ZHUmVwb190cmFjZXJDYXJyaWVyJyBzdHJva2UtbGluZWNhcD0ncm91bmQnIHN0cm9rZS1saW5lam9pbj0ncm91bmQnLz48ZyBpZD0nU1ZHUmVwb19pY29uQ2Fycmllcic+PHBhdGggZD0nTTIwLjY5MzMgMTcuMzI5NEMyMS4wNTA2IDE1Ljk5NTkgMjEuMDk2NCAxNC41OTgyIDIwLjgyNzEgMTMuMjQ0MkMyMC41NTc3IDExLjg5MDIgMTkuOTgwNiAxMC42MTY0IDE5LjE0MDIgOS41MjExNUMxOC4yOTk4IDguNDI1OTMgMTcuMjE4NyA3LjUzODcyIDE1Ljk4MDYgNi45MjgxNUMxNC43NDI1IDYuMzE3NTcgMTMuMzgwNSA2IDEyIDZDMTAuNjE5NSA2IDkuMjU3NTIgNi4zMTc1NyA4LjAxOTQgNi45MjgxNUM2Ljc4MTI4IDcuNTM4NzIgNS43MDAyMSA4LjQyNTkzIDQuODU5ODIgOS41MjExNUM0LjAxOTQzIDEwLjYxNjQgMy40NDIyNSAxMS44OTAyIDMuMTcyOTMgMTMuMjQ0MkMyLjkwMzYxIDE0LjU5ODIgMi45NDkzNyAxNS45OTU5IDMuMzA2NjcgMTcuMzI5NCcgc3Ryb2tlPScjM0Y1MUI1JyBzdHJva2Utd2lkdGg9JzInIHN0cm9rZS1saW5lY2FwPSdyb3VuZCcvPjxwYXRoIGQ9J00xMi43NjU3IDE1LjU4MjNDMTMuMjUzMiAxNi4yOTE2IDEyLjkxMDQgMTcuMzczOCAxMiAxNy45OTk0QzExLjA4OTcgMTguNjI1IDkuOTU2NTIgMTguNTU3MSA5LjQ2OTA2IDE3Ljg0NzdDOC45NDk1NSAxNy4wOTE3IDcuMTU2MTYgMTIuODQwOSA2LjA2NzEzIDEwLjIxMTRDNS44NjIwMyA5LjcxNjIxIDYuNDY3NyA5LjMgNi44NTY0OCA5LjY2OUM4LjkyMDc3IDExLjYyODMgMTIuMjQ2MiAxNC44MjYzIDEyLjc2NTcgMTUuNTgyM1onIHN0cm9rZT0nIzNGNTFCNScgc3Ryb2tlLXdpZHRoPScyJy8+PHBhdGggZD0nTTEyIDZWOCcgc3Ryb2tlPScjM0Y1MUI1JyBzdHJva2Utd2lkdGg9JzInIHN0cm9rZS1saW5lY2FwPSdyb3VuZCcvPjxwYXRoIGQ9J001LjYzNTk5IDguNjM1NzRMNy4wNTAyIDEwLjA1JyBzdHJva2U9JyMzRjUxQjUnIHN0cm9rZS13aWR0aD0nMicgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+PHBhdGggZD0nTTE4LjM2NCA4LjYzNTc0TDE2Ljk0OTggMTAuMDUnIHN0cm9rZT0nIzNGNTFCNScgc3Ryb2tlLXdpZHRoPScyJyBzdHJva2UtbGluZWNhcD0ncm91bmQnLz48cGF0aCBkPSdNMjAuNjkzNCAxNy4zMjkxTDE4Ljc2MTUgMTYuODExNScgc3Ryb2tlPScjM0Y1MUI1JyBzdHJva2Utd2lkdGg9JzInIHN0cm9rZS1saW5lY2FwPSdyb3VuZCcvPjxwYXRoIGQ9J00zLjMwNjY0IDE3LjMyOTFMNS4yMzg0OSAxNi44MTE1JyBzdHJva2U9JyMzRjUxQjUnIHN0cm9rZS13aWR0aD0nMicgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+PC9nPjwvc3ZnPg==");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        display: inline-block;
    }
    .cwc-progress-determinate, .progress-arrow, .arrow-text {
        transition: width 1s ease, left 1s ease;
    }
    .follow-icon-container {
        padding-bottom: 15px;
    }
    .cwc-acc-style:focus {
        outline: 3px solid #FFC400 !important;
    }
    .cwc-tide-table, .cwc-tide-th, .cwc-tide-td {
        border: 1px solid black;
    }
    .cwc-tide-table {
        border-collapse: collapse;
        width: 90%;
        min-height: 190px;
    }
    .cwc-tide-th, .cwc-tide-td {
        padding-left: 8px;
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: left;
        vertical-align: middle;
    }
    .cwc-tr-head {
        background-color: rgba(0, 0, 0, 0.1);
    }
    .cwc-checkbox-setdefault {
        margin-top: 5px;
    }
    .switch-label {
        margin-right: 10px;
    }
    .cwc-text-tips {
        font-size: 15px;
        line-height: 1.3;
        font-weight: bold;
        margin-top: -12px;
        margin-bottom: 18px;
    }
    .cwc-noalerts {
        margin-top: -10px;
    }
    .cwc-banner {
        width: 100%;
        background-color: #FFC740;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
        font-size: 15px;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .cwc-history-item {
        display: flex;
        align-items: center;
        padding: 8px;
        transition: height 0.3s ease;
        overflow: hidden;
    }
    .cwc-history-icon {
        margin-right: 6px;
        display: flex;
        align-items: center;
    }
    .cwc-radio {
      position: relative;
      display: inline-block;
      height: 32px;
      padding-left: 22px;
      font-size: 14px;
      margin-right: 16px;
      line-height: 32px;
      font-weight: bold;
      cursor: pointer;
      -webkit-user-select: none;
         -moz-user-select: none;
          -ms-user-select: none;
              user-select: none;
    }
    .cwc-radio input {
      position: absolute;
      width: 0;
      height: 0;
      overflow: hidden;
      opacity: 0;
    }
    .cwc-radio-icon {
      position: absolute;
      top: 7px;
      left: 0;
      display: inline-block;
      -webkit-box-sizing: border-box;
              box-sizing: border-box;
      width: 18px;
      height: 18px;
      vertical-align: middle;
      border: 2px solid rgba(0, 0, 0, 0.65);
      border-radius: 18px;
      -webkit-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cwc-radio-icon::before {
        position: absolute;
        top: 0px;
        left: 0;
        width: 14px;
        height: 14px;
        background-color: #1976D2;
        border-radius: 14px;
        -webkit-transform: scale(0);
        transform: scale(0);
      opacity: 0;
      -webkit-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      content: ' ';
    }
    .cwc-radio input[type="radio"]:checked + .cwc-radio-icon {
        border-color: #1976D2;
    }
    .cwc-radio input[type="radio"]:checked + .cwc-radio-icon::before {
      -webkit-transform: scale(0.68);
              transform: scale(0.68);
      opacity: 1;
    }
    .settings-container {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
    .toggle-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .toggle-label {
        flex-grow: 1;
    }
    .cwc-group-btn {
        border: 1px solid #000;
        width: 42px;
        padding: 0px;
        background-color: transparent;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    .cwc-group-btn:hover {
        width: 110px;
        background-color: #000;
    }
    .cwc-group-btn svg {
        color: #000;
        fill: #000;
        transition: color 0.3s ease, fill 0.3s ease;
    }
    .cwc-group-btn:hover svg {
        color: #FFF;
        fill: #FFF;
    }
    .group-btn-text {
        white-space: nowrap;
        font-size: 14px;
        font-weight: bold;
        font-family: 'misans', sans-serif;
        color: #000;
        max-height: 0;
        max-width: 0;
        overflow: hidden;
        opacity: 0;
        transition: opacity 0.3s ease, max-height 0.3s ease 0.3s;
    }
    .cwc-group-btn:hover .group-btn-text {
        margin-left: 4px;
        color: #FFF;
        opacity: 1;
        max-height: 100px;
        max-width: 100px;
        transition: max-width 0.3s ease;
    }
    .cwc-group-btn:disabled {
        opacity: 0.4;
    }
    .cwc-group-btn:disabled svg {
        color: #000;
        fill: #000;
    }
    .cwc-group-btn:hover:disabled svg {
        color: #000;
        fill: #000;
    }
    .cwc-group-btn:hover:disabled {
        width: 42px;
        background-color: transparent;
    }
    .group-btn-text:disabled {
        disable: none;
    }
    .cwc-group-btn:hover:disabled .group-btn-text {
        display: none;
    }
    .rotate-svg {
        animation: rotateAnimation 0.5s ease forwards;
    }
    .needload-tip {
        opacity: 0;
        position: relative;
        top: -10px;
        color: #EF5350;
        font-size: 13px;
        font-weight: bold;
    }
    .no-wrap {
        white-space: nowrap; 
    }
    .menu-locate-btn {
        position: fixed;
        right: 50px;
        top: 17px;
        border: none;
        background-color: transparent;
        cursor: pointer;
    }
    @keyframes breathe-effect {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.6); }
    }
    #getLocation {
        animation-name: none;
    }
    @keyframes shrink {
        to { transform: scale(0); }
    }
    @keyframes grow {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }
    .menu-locate-btn {
        transform-origin: center;
    }
    @media (max-width: 399px) {
        .switch-toggle {
            right: 15px;
        }
    }
    @media (min-width: 400px) and (max-width: 499px) {
        .switch-toggle {
            right: 30px;
        }
    }
    @media (min-width: 500px) and (max-width: 599px) {
        .switch-toggle {
            right: 60px;
        }
    }
    @media (min-width: 600px) and (max-width: 1023px) {
        .switch-toggle {
            right: 120px;
        }
    }
    @media (min-width: 1024px) {
        .switch-toggle {
            right: 240px;
        }
    }
    @media (prefers-color-scheme: dark) {
        #suggestions {
            background-color: #121212;
            color: #FFF;
            box-shadow: 0px 2px 5px rgba(255,255,255,0.5);
        }
        ::selection {
            background-color: #FFF;
            color: rgba(0, 0, 0, 0.8);
        }
        #suggestions {
            border: 1px solid #FFF;
        }
        #suggestions li:hover {
            background-color: #FFF;
            color: #000;
        }
        #suggestions .cwc-history-item:hover {
            background-color: #FFF;
            color: #000;
        }
        #suggestions .cwc-history-item:hover .cwc-history-icon svg {
            fill: #000;
        }
        #suggestions .cwc-history-item .cwc-history-icon svg {
            fill: #FFF;
        }
        #weather-alerts {
            background-color: rgba(165, 214, 167, 0.5);
        }
        #weather-alerts-iosok {
            background-color: rgba(165, 214, 167, 0.5);
        }
        header {
            background-color: #121212;
        }
        footer {
            background-color: #121212;
        }
        .c-w-c {
            color: #FFF !important;
        }
        .cwc-result-ok-container {
            background-color: rgba(165, 214, 167, 0.5);
        }
        .cwc-result-err-container {
            background-color: rgba(239, 83, 80, 0.5);
        }
        .cwc-checkbox {
            border-color: white;
            background-color: transparent;
        }
        .cwc-checkbox:checked {
            background-color: white;
            border-color: white;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23000000" viewBox="0 0 24 24"><path d="M 20.292969 5.2929688 L 9 16.585938 L 4.7070312 12.292969 L 3.2929688 13.707031 L 9 19.414062 L 21.707031 6.7070312 L 20.292969 5.2929688 z"/></svg>');
        }
        .cwc-footer-link {
            color: white;
        }
        .cwc-footer-link:visited {
            color: white;
        }
        .cwc-footer-link:after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%23FFFFFF"/></svg>');
        }
        .cwc-footer-link-noafter {
            color: white;
        }
        .cwc-footer-link-noafter:visited {
            color: white;
        }
        .footer-border {
            border-color: white !important;
        }
        .cwc-footer-top {
            color: white;
        }
        .cwc-footer-top:visited {
            color: white;
        }
        .cwc-btn {
            border-color: white;
            background-color: transparent;
            color: white;
        }
        .cwc-btn:hover {
            background-color: white;
            color: black;
        }
        .cwc-cookies {
            border-color: white !important;
        }
        .cwc-selection {
            border-color: white !important;
            color: white !important;
        }
        .cwc-options {
            background-color: #212121;
            border-color: white;
            box-shadow: 0px 2px 5px rgba(255, 255, 255, 0.5);
        }
        .cwc-option:hover {
            background-color: white;
            color: black;
        }
        .cwc-selection:hover {
            background-color: black;
            color: white;
        }
        .shadow {
            box-shadow: 2px 4px 2px -2px rgba(255, 255, 255, 0.5);
        }
        .cwc-menu {
            background-color: #121212 !important;
            border-color: white !important;
        }
        .cwc-cookies {
            background-color: #121212 !important;
        }
        body {
            background-color: #212121;
            color: #FFF;
            transition: color 0.3s ease;
        }
        .cwc-input {
            border-color: white;
            color: white;
        }
        .cwc-icon-btn {
            border-color: white;
        }
        .cwc-icon-btn svg {
            fill: white;
        }
        .cwc-icon-btn:hover {
            background-color: white;
        }
        .cwc-icon-btn:hover svg {
            fill: black;
        }
        .cwc-icon-btn svg polyline,
        .cwc-icon-btn svg line {
            stroke: white;
        }
        .cwc-icon-btn:hover svg polyline,
        .cwc-icon-btn:hover svg line {
            stroke: black;
        }
        .footer {
            border-top: 1px solid white !important;
        }
        .cwc-svg path,
        .cwc-svg rect {
            fill: white !important;
        }
        .menu-svg polyline {
            stroke: white !important;
        }
        .totop-btn polyline, .totop-btn line {
            stroke: white !important;
        }
        .cwc-tide-table, .cwc-tide-th, .cwc-tide-td {
          border-color: white;
        }
        .cwc-tr-head {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .cwc-group-btn {
            border-color: #FFF;
            background-color: transparent;
        }
        .cwc-group-btn:hover {
            background-color: #FFF;
        }
        .cwc-group-btn svg {
            color: #FFF;
            fill: #FFF;
            transition: color 0.3s ease, fill 0.3s ease;
        }
        .cwc-group-btn:hover svg {
            color: #000;
            fill: #000;
        }
        .group-btn-text {
            color: #FFF;
        }
        .cwc-group-btn:hover .group-btn-text {
            color: #000;
        }
        .cwc-group-btn:disabled svg {
            color: #FFF;
            fill: #FFF;
        }
        .cwc-group-btn:hover:disabled svg {
            color: #FFF;
            fill: #FFF;
        }
        .cwc-svg-done {
            background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyNmZmZmZmYnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFptNDguMTksMTIxLjQyLDI0LjEsMjEuMDYtNzMuNjEsODQuMS0yNC4xLTIzLjA2Wk0xOTEuOTMsMzQyLjYzLDEyMS4zNywyNzIsMTQ0LDI0OS4zNywyMTQuNTcsMzIwWm02NSwuNzlMMTg1LjU1LDI3MmwyMi42NC0yMi42Miw0Ny4xNiw0Ny4yMUwzNjYuNDgsMTY5LjQybDI0LjEsMjEuMDZaJy8+PC9zdmc+IA==");
        }
        .cwc-svg-checked {
            background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyNmZmZmZmYnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFpNMjE4LDM2MC4zOCwxMzcuNCwyNzAuODFsMjMuNzktMjEuNDEsNTYsNjIuMjJMMzUwLDE1My40NiwzNzQuNTQsMTc0WicvPjwvc3ZnPiA=");
        }
        .cwc-svg-error {
            background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwYXRoIGZpbGw9JyNmZmZmZmYnIGQ9J00yNTYsNDhDMTQxLjMxLDQ4LDQ4LDE0MS4zMSw0OCwyNTZzOTMuMzEsMjA4LDIwOCwyMDgsMjA4LTkzLjMxLDIwOC0yMDhTMzcwLjY5LDQ4LDI1Niw0OFptODYuNjMsMjcyTDMyMCwzNDIuNjNsLTY0LTY0LTY0LDY0TDE2OS4zNywzMjBsNjQtNjQtNjQtNjRMMTkyLDE2OS4zN2w2NCw2NCw2NC02NEwzNDIuNjMsMTkybC02NCw2NFonLz48L3N2Zz4g");
        }
        .cwc-svg-alert {
            background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNScgaGVpZ2h0PScyNScgdmlld0JveD0nMCAwIDUxMiA1MTInPjxwb2x5Z29uIGZpbGw9JyNmZmZmZmYnIHBvaW50cz0nMjQwIDMwNCAyNzIgMzA0IDI3OCAxNDQgMjM0IDE0NCAyNDAgMzA0JyBzdHlsZT0nZmlsbDpub25lJy8+PHBhdGggZmlsbD0nI2ZmZmZmZicgZD0nTTI1Niw0OEMxNDEuMzEsNDgsNDgsMTQxLjMxLDQ4LDI1NnM5My4zMSwyMDgsMjA4LDIwOCwyMDgtOTMuMzEsMjA4LTIwOFMzNzAuNjksNDgsMjU2LDQ4Wm0yMCwzMTkuOTFIMjM2di00MGg0MFpNMjcyLDMwNEgyNDBsLTYtMTYwaDQ0WicvPjwvc3ZnPiA=");
        }
        .cwc-svg-pin {
            background-image: url("data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScyNXB4JyBoZWlnaHQ9JzI1cHgnIGZpbGw9JyNGRkZGRkYnIHZpZXdCb3g9JzAgMCA1MTIgNTEyJz48cGF0aCBkPSdNMjU2IDQ4QTIwOC4yMyAyMDguMjMgMCAwMDQ4IDI1NmMwIDExNC42OCA5My4zMSAyMDggMjA4IDIwOGEyMDguMjMgMjA4LjIzIDAgMDAyMDgtMjA4YzAtMTE0LjY5LTkzLjMxLTIwOC0yMDgtMjA4em0tOCAzNjFWMjY0SDEwM2wyNTktMTE0LjExeicvPjwvc3ZnPiA=");
        }
    }
    </style>
</head>
<body>
    <header>
        <button class="menu-locate-btn" id="getLocation">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" class="cwc-svg" viewBox="0 0 512 512"><path d="M272 464a16 16 0 01-16-16.42V264.13a8 8 0 00-8-8H64.41a16.31 16.31 0 01-15.49-10.65 16 16 0 018.41-19.87l384-176.15a16 16 0 0121.22 21.19l-176 384A16 16 0 01272 464z"/></svg>
        </button>
        <div class="cwc-svg-right">
            <button id="menuBtn" class="header-btn cwc-menu-btn cwc-acc">
                <svg class="menu-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512"><polyline points="112 184 256 328 400 184" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/></svg>
            </button>
        </div>
    
        <button id="backtopBtn" class="header-btn cwc-menu-btn cwc-acc">
            <svg class="totop-btn" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512"><polyline points="112 244 256 100 400 244" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/><line x1="256" y1="120" x2="256" y2="412" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/></svg>
        </button>
    
        <div class="cwc-flex">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512" style="position: relative; top: 14px; margin-right: 6px;" class="cwc-svg cwc-acc" aria-label="Caner Weather Channel 的 LOGO"><path d="M340,480H106c-29.5,0-54.92-7.83-73.53-22.64C11.23,440.44,0,415.35,0,384.8c0-26.66,10.08-49.8,29.14-66.91,15.24-13.68,36.17-23.21,59-26.84h0c.06,0,.08,0,.09-.05,6.44-39,23.83-72.09,50.31-95.68A140.24,140.24,0,0,1,232,160c30.23,0,58.48,9.39,81.71,27.17a142.69,142.69,0,0,1,45.36,60.66c29.41,4.82,54.72,17.11,73.19,35.54C453,304.11,464,331.71,464,363.2c0,32.85-13.13,62.87-37,84.52C404.11,468.54,373.2,480,340,480Zm19-232.18Z"/><path d="M381.5,219.89a169.23,169.23,0,0,1,45.44,19A96,96,0,0,0,281,129.33q-2.85,2-5.54,4.2a162.47,162.47,0,0,1,57.73,28.23A174.53,174.53,0,0,1,381.5,219.89Z"/><rect x="448" y="192" width="64" height="32"/><rect x="320" y="32" width="32" height="64"/><path d="M255.35,129.63l12.45-12.45L223.18,72.55,200.55,95.18l33.17,33.17h.6A172,172,0,0,1,255.35,129.63Z"/><rect x="406.27" y="90.18" width="63.11" height="32" transform="translate(53.16 340.68) rotate(-45)"/></svg> 
            <h2 id="cwc-title" class="cwc-title c-w-c"><a href="https://weather.caner.center" style="text-decoration: none; color: inherit; visited: inherit;" class="cwc-acc"><span class="hide-s">Caner </span>Weather<span class="hide-xs"> Channel</span></a></h2>
        </div>
    </header>
    
    <nav class="cwc-menu">
        <div class="cwc-menu-content">
        
            <form id="langForm" action="" method="post">
                <div class="cwc-menu-label cwc-acc" for="custom-lang">&darr; <?php echo $lang['lang_region']; ?></div>
                <div class="cwc-horizontal-container" id="lang-components">
                <div class="cwc-selection cwc-acc" id="custom-lang">
                    <div class="selection-value"><?php echo $lang['choose_lang_region']; ?></div>
                    <div class="cwc-options">
                        <div class="cwc-option cwc-acc" data-value="zh-CN">简体中文 (中国大陆)</div>
                        <div class="cwc-option cwc-acc" data-value="zh-HK">繁體中文 (香港)</div>
                        <div class="cwc-option cwc-acc" data-value="zh-TW">繁體中文 (台灣)</div>
                        <div class="cwc-option cwc-acc" data-value="en-US">English (United States)</div>
                        <div class="cwc-option cwc-acc" data-value="uk-UA">українська (Україна)</div>
                        <div class="cwc-option cwc-acc" data-value="hi">हिंदी (भारत)</div>
                    </div>
                </div>
                <input type="hidden" name="Lang" id="Lang" value="*">
                <button class="cwc-icon-btn cwc-acc" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M267 474l-.8-.13a.85.85 0 00.8.13zM448.9 187.78a5.51 5.51 0 00-10.67-.63A5.52 5.52 0 01433 191h-15.47a5.48 5.48 0 01-2.84-.79l-22.38-13.42a5.48 5.48 0 00-2.84-.79h-35.8a5.48 5.48 0 00-3.06.93l-44.15 29.43A5.52 5.52 0 00304 211v41.74a5.51 5.51 0 002.92 4.87l57.89 30.9a5.55 5.55 0 012.92 4.8l.27 23.49a5.53 5.53 0 002.85 4.75l23.26 12.87a5.54 5.54 0 012.85 4.83v48.6a5.52 5.52 0 009.17 4.14c9.38-8.26 22.83-20.32 24.62-23.08q4.44-6.87 8.33-14.07a207.39 207.39 0 0013.6-31c12.68-36.71 2.66-102.7-3.78-136.06zM286.4 302.8l-61.33-46a4 4 0 00-2.4-.8h-29.1a3.78 3.78 0 01-2.68-1.11l-13.72-13.72a4 4 0 00-2.83-1.17h-53.19a3.79 3.79 0 01-2.68-6.47l8.42-8.42a3.78 3.78 0 012.68-1.11h32.37a8 8 0 007.7-5.83l6.89-24.5a4 4 0 012-2.47L206 177.06a3.79 3.79 0 002.05-3.37v-12.5a3.82 3.82 0 01.68-2.17l14.6-21.02a3.75 3.75 0 011.78-1.38l20.43-7.67a3.79 3.79 0 002.46-3.55V114a3.8 3.8 0 00-1.69-3.16l-20.48-13.62A3.83 3.83 0 00222 97l-27.88 13.94a3.78 3.78 0 01-4-.41l-13.22-10.45a3.8 3.8 0 01.1-6l10.74-7.91a3.78 3.78 0 00-.09-6.16l-16.73-11.67a3.78 3.78 0 00-4-.22c-6.05 3.31-23.8 13.11-30.1 17.52a209.48 209.48 0 00-68.16 80c-1.82 3.76-4.07 7.59-4.29 11.72s-3.46 13.35-4.81 17.08a3.78 3.78 0 00.24 3.1l35.69 65.58a3.74 3.74 0 001.38 1.44l37.55 22.54a3.78 3.78 0 011.81 2.73l7.52 54.54a3.82 3.82 0 001.61 2.61l29.3 20.14a4 4 0 011.65 2.48l15.54 73.8a3.6 3.6 0 00.49 1.22c1.46 2.36 7.28 11 14.3 12.28-.65.18-1.23.59-1.88.78a47.63 47.63 0 015 1.16c2 .54 4 1 6 1.43 3.13.62 3.44 1.1 4.94-1.68 2-3.72 4.29-5 6-5.46a3.85 3.85 0 002.89-2.9l10.07-46.68a4 4 0 011.6-2.42l45-31.9a4 4 0 001.69-3.27V306a4 4 0 00-1.55-3.2z"/><path d="M262 48s-3.65.21-4.39.23q-8.13.24-16.22 1.12A207.45 207.45 0 00184.21 64c2.43 1.68-1.75 3.22-1.75 3.22L189 80h35l24 12 21-12zM354.23 120.06l16.11-14a4 4 0 00-.94-6.65l-18.81-8.73a4 4 0 00-5.3 1.9l-7.75 16.21a4 4 0 001.49 5.11l10.46 6.54a4 4 0 004.74-.38zM429.64 140.67l-5.83-9c-.09-.14-.17-.28-.25-.43-1.05-2.15-9.74-19.7-17-26.51-5.45-5.15-7-3.67-7.43-2.53a3.77 3.77 0 01-1.19 1.6l-28.84 23.31a4 4 0 01-2.51.89h-14.93a4 4 0 00-2.83 1.17l-12 12a4 4 0 000 5.66l12 12a4 4 0 002.83 1.17h75.17a4 4 0 004-4.17l-.55-13.15a4 4 0 00-.64-2.01z"/><path d="M256 72a184 184 0 11-130.1 53.9A182.77 182.77 0 01256 72m0-40C132.3 32 32 132.3 32 256s100.3 224 224 224 224-100.3 224-224S379.7 32 256 32z"/></svg>
                </button>
                </div>
                <div class="cwc-horizontal-container cwc-checkbox-setdefault cwc-acc">
                <input type="checkbox" name="setDefaultLang" id="setDefaultLang" class="cwc-acc" aria-label="<?php echo $lang['set_default_checkbox']; ?>">
                <label for="setDefaultLang" class="cwc-checkbox-label cwc-acc"><?php echo $lang['set_default']; ?></label>
                </div>
            </form>
        
            <form id="unitsForm" action="" method="post">
                <div class="cwc-menu-label cwc-menu-interval cwc-acc" for="custom-units">&darr; <?php echo $lang['weather_units']; ?></div>
                <div class="cwc-horizontal-container" id="units-components">
                <div class="cwc-selection cwc-acc" id="custom-units">
                    <div class="selection-value"><?php echo $lang['choose_weather_units']; ?></div>
                    <div class="cwc-options">
                        <div class="cwc-option cwc-acc" data-value="imperial"><?php echo $lang['imperial']; ?></div>
                        <div class="cwc-option cwc-acc" data-value="metric"><?php echo $lang['metric']; ?></div>
                        <div class="cwc-option cwc-acc" data-value="standard"><?php echo $lang['standard']; ?></div>
                    </div>
                </div>
                <input type="hidden" name="Units" id="Units" value="<?php echo $units; ?>">
                <button class="cwc-icon-btn cwc-acc" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512">
                        <path d="M473.66,210c-16.56-12.3-37.7-20.75-59.52-24-6.62-39.18-24.21-72.67-51.3-97.45C334.15,62.25,296.21,47.79,256,47.79c-35.35,0-68,11.08-94.37,32.05a149.61,149.61,0,0,0-45.32,60.49c-29.94,4.6-57.12,16.68-77.39,34.55C13.46,197.33,0,227.24,0,261.39c0,34.52,14.49,66,40.79,88.76,25.12,21.69,58.94,33.64,95.21,33.64H240V230.42l-48,48-22.63-22.63L256,169.17l86.63,86.62L320,278.42l-48-48V383.79H396c31.34,0,59.91-8.8,80.45-24.77,23.26-18.1,35.55-44,35.55-74.83C512,254.25,498.74,228.58,473.66,210Z"/>
                        <rect x="240" y="383.79" width="32" height="80.41"/>
                    </svg>
                </button>
                </div>
                <div class="cwc-horizontal-container cwc-checkbox-setdefault cwc-acc">
                <input type="checkbox" name="setDefaultUnits" id="setDefaultUnits" class="cwc-acc" aria-label="<?php echo $lang['set_default_checkbox']; ?>">
                <label for="setDefaultUnits" class="cwc-checkbox-label cwc-acc"><?php echo $lang['set_default']; ?></label>
                </div>
            </form>

            <form id="mapsForm" action="" method="post">
                <div class="cwc-menu-label cwc-menu-interval cwc-acc" for="custom-maps">&darr; <?php echo $lang['maps_provider']; ?></div>
                <div class="cwc-horizontal-container" id="maps-components">
                <div class="cwc-selection cwc-acc" id="custom-maps">
                    <div class="selection-value"><?php echo $lang['choose_maps_provider']; ?></div>
                    <div class="cwc-options">
                        <div class="cwc-option cwc-acc" data-value="GMaps"><?php echo $lang['gmap']; ?></div>
                        <div class="cwc-option cwc-acc" data-value="AMap"><?php echo $lang['amap']; ?></div>
                        <div class="cwc-option cwc-acc" data-value="AutoMaps"><?php echo $lang['automap']; ?></div>
                    </div>
                </div>
                <input type="hidden" name="Maps" id="Maps" value="<?php echo $maps; ?>">
                <button class="cwc-icon-btn cwc-acc" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><polyline points="465 127 241 384 149 292" style="fill:none;stroke-linecap:square;stroke-miterlimit:10;stroke-width:44px"/><line x1="140" y1="385" x2="47" y2="292" style="fill:none;stroke-linecap:square;stroke-miterlimit:10;stroke-width:44px"/><line x1="363" y1="127" x2="236" y2="273" style="fill:none;stroke-linecap:square;stroke-miterlimit:10;stroke-width:44px"/></svg>
                </button>
                </div>
            </form>
            
        <div class="cwc-menu-label cwc-menu-interval cwc-acc">&darr; <?php echo $lang['acc']; ?></div>
        <div class="cwc-horizontal-container cwc-acc settings-container">
            <div class="toggle-container">
                <label for="acc-checkbox" class="cwc-checkbox-label switch-label cwc-acc toggle-label"><?php echo $lang['acc_vision_tts']; ?></label>
                <input id="acc-checkbox" type="checkbox" class="switch switch-toggle cwc-acc" name="acc-checkbox" aria-label="<?php echo $lang['acc_toggle']; ?>" <?php echo $acc ? 'checked' : ''; ?>>
            </div>
            <div class="toggle-container">
                <label for="apo-checkbox" class="cwc-checkbox-label switch-label cwc-acc toggle-label"><?php echo $lang['acc_autocomplete']; ?></label>
                <input id="apo-checkbox" type="checkbox" class="switch switch-toggle cwc-acc" name="apo-checkbox" aria-label="<?php echo $lang['acc_autocomplete_toggle']; ?>" <?php echo ($autocomplete === "browser") ? 'checked' : ''; ?>>
            </div>
            <div id="needReload" class="needload-tip"><?php echo $lang['acc_need_reload']; ?>&nbsp;&nbsp;&nbsp;&nbsp;<span onclick="location.reload();" style="cursor: pointer; color: #FFC107;">&#8635; <?php echo $lang['reload']; ?></span></div>
        </div>


        </div>
        
        <div class="cwc-btn-group">
            <button class="cwc-group-btn cwc-acc" <?php echo isset($current) ? '' : 'disabled'; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M480 208H308L256 48l-52 160H32l140 96-54 160 138-100 138 100-54-160z" fill="none" stroke="currentColor" stroke-width="32"></path></svg>
                <span class="group-btn-text"><?php echo $lang['collect_place']; ?></span>
            </button>
            <button class="cwc-group-btn cwc-acc" id="shareWeather" <?php echo isset($current) ? '' : 'disabled'; ?>>
                <svg id="shareSvg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M378 324a69.78 69.78 0 00-48.83 19.91L202 272.41a69.68 69.68 0 000-32.82l127.13-71.5A69.76 69.76 0 10308.87 129l-130.13 73.2a70 70 0 100 107.56L308.87 383A70 70 0 10378 324z"/></svg>
                <span id="shareTxt" class="group-btn-text"><?php echo $lang['share_weather']; ?></span>
            </button>
            <a href="./cookies" class="cwc-group-btn cwc-acc">
                <svg viewBox="0 0 120.23 122.88" width="24" height="24"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;}</style><g><path class="st0" d="M98.18,0c3.3,0,5.98,2.68,5.98,5.98c0,3.3-2.68,5.98-5.98,5.98c-3.3,0-5.98-2.68-5.98-5.98 C92.21,2.68,94.88,0,98.18,0L98.18,0z M99.78,52.08c5.16,7.7,11.69,10.06,20.17,4.85c0.28,2.9,0.35,5.86,0.2,8.86 c-1.67,33.16-29.9,58.69-63.06,57.02C23.94,121.13-1.59,92.9,0.08,59.75C1.74,26.59,30.95,0.78,64.1,2.45 c-2.94,9.2-0.45,17.37,7.03,20.15C64.35,44.38,79.49,58.63,99.78,52.08L99.78,52.08z M30.03,47.79c4.97,0,8.99,4.03,8.99,8.99 s-4.03,8.99-8.99,8.99c-4.97,0-8.99-4.03-8.99-8.99S25.07,47.79,30.03,47.79L30.03,47.79z M58.35,59.25c2.86,0,5.18,2.32,5.18,5.18 c0,2.86-2.32,5.18-5.18,5.18c-2.86,0-5.18-2.32-5.18-5.18C53.16,61.57,55.48,59.25,58.35,59.25L58.35,59.25z M35.87,80.59 c3.49,0,6.32,2.83,6.32,6.32c0,3.49-2.83,6.32-6.32,6.32c-3.49,0-6.32-2.83-6.32-6.32C29.55,83.41,32.38,80.59,35.87,80.59 L35.87,80.59z M49.49,32.23c2.74,0,4.95,2.22,4.95,4.95c0,2.74-2.22,4.95-4.95,4.95c-2.74,0-4.95-2.22-4.95-4.95 C44.54,34.45,46.76,32.23,49.49,32.23L49.49,32.23z M76.39,82.8c4.59,0,8.3,3.72,8.3,8.3c0,4.59-3.72,8.3-8.3,8.3 c-4.59,0-8.3-3.72-8.3-8.3C68.09,86.52,71.81,82.8,76.39,82.8L76.39,82.8z M93.87,23.1c3.08,0,5.58,2.5,5.58,5.58 c0,3.08-2.5,5.58-5.58,5.58s-5.58-2.5-5.58-5.58C88.29,25.6,90.79,23.1,93.87,23.1L93.87,23.1z"/></g></svg>
                <span class="group-btn-text"><?php echo $lang['cookies_manage']; ?></span>
            </a>
            <button class="cwc-group-btn cwc-acc" onclick="document.getElementById('needRotate').classList.add('rotate-svg'); setTimeout(function() { document.getElementById('needRotate').classList.remove('rotate-svg'); }, 500);">
                <svg xmlns="http://www.w3.org/2000/svg" id="needRotate" width="24" height="24" viewBox="0 0 512 512"><path d="M414.39 97.61A224 224 0 1097.61 414.39 224 224 0 10414.39 97.61zM256 432v-96a80 80 0 010-160V80c97.05 0 176 79 176 176s-78.95 176-176 176z"/><path d="M336 256a80 80 0 00-80-80v160a80 80 0 0080-80z"/></svg>
                <span class="group-btn-text"><?php echo $lang['color_scheme']; ?></span>
            </button>

        </div>
        
    </nav>
    <main>
    
    <div class="cwc-cookies" id="cwc-cookies">
        <p class="cookies-title cwc-acc" id="cookies-title-text"><?php echo $lang['cookies_title']; ?></p>
        <p class="cookies-content cwc-acc"><?php echo $lang['cookies_content']; ?></p>
        <div class="cwc-btn cwc-acc" id="cookies-close"><?php echo $lang['cookies_close']; ?></div>
        <a href="./cookies" class="cwc-btn cwc-acc"><?php echo $lang['cookies_goto_manage']; ?></a>
    </div>
    
    <form class="cwc-from" method="get">
        <div class="cwc-from-container">
            <div class="cwc-flex-center-container">
                <input class="cwc-input cwc-acc" type="text" name="location" id="location" placeholder="<?php echo $lang['input_placeholder']; ?>" aria-label="<?php echo $lang['input_placeholder_arai']; ?>" <?php echo ($autocomplete === "cwc") ? 'autocomplete="off"' : ''; ?>>
                <button class="cwc-icon-btn cwc-acc" type="submit" id="btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M464,428,339.92,303.9a160.48,160.48,0,0,0,30.72-94.58C370.64,120.37,298.27,48,209.32,48S48,120.37,48,209.32s72.37,161.32,161.32,161.32a160.48,160.48,0,0,0,94.58-30.72L428,464ZM209.32,319.69A110.38,110.38,0,1,1,319.69,209.32,110.5,110.5,0,0,1,209.32,319.69Z"/></svg>
                </button>
                <button class="cwc-close-btn cwc-svg" aria-label="<?php echo $lang['input_clear']; ?>" id="closeBtn"><svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 512 512"><path d="M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z"/></svg>
                </button>
            </div>
            <ul id="suggestions" class="cwc-acc"></ul>
        </div>
    </form>
    
    <?php if (isset($current)): ?>
    
    <div class="cwc-result-ok-container">
        <div class="cwc-svg-pin"></div>
        <div class="cwc-alert-tip">
            <h3 class="cwc-acc"><?php echo htmlspecialchars($locationName); ?></h3>
            <div class="cwc-text-tips cwc-acc">
            <?php echo $lang['lat']; ?>: <?php echo $latitude; ?> 
            <?php echo $lang['lon']; ?>: <?php echo $longitude; ?><br>
            <?php echo $lang['tz']; ?>: <?php echo $GCountryCode; ?> | <?php echo $timezone; ?> (<?php echo formatUtcOffset($timezone_offset); ?>)<br>
            <?php echo $lang['units']; ?>: <?php echo getUnitDescription($units); ?><br>
            </div>
        </div>
    </div>
    
    <?php if ($maps === "GMaps"): ?>
        <div class="cwc-map cwc-acc" id="map">
            <h3 class="cwc-center cwc-acc"><?php echo $lang['loading_gmap']; ?></h3>
        </div>
        <script>
        var mapLoaded = false;
        function initGMap() {
            <?php if (isset($latitude) && isset($longitude)): ?>
            var latitude = <?= json_encode($latitude) ?>;
            var longitude = <?= json_encode($longitude) ?>;
            var location = { lat: latitude, lng: longitude };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 8,
                center: location
            });
        
            var controlDiv = document.createElement('div');
            var controlUI = document.createElement('button');
            controlUI.style.cssText = "background-color: #fff; border: 2px solid #fff; border-radius: 2px; box-shadow: 0 2px 6px rgba(0,0,0,.3); cursor: pointer; margin-bottom: 5px; margin-left: 10px; text-align: center;";
            controlUI.title = '<?php echo $lang['map_check_radar']; ?>';
            controlUI.setAttribute('aria-label', '<?php echo $lang['map_check_radar']; ?>');
            controlDiv.appendChild(controlUI);
            map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(controlDiv);
        
            var svgIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgIcon.setAttribute('height', '32');
            svgIcon.setAttribute('viewBox', '0 0 24 24');
            svgIcon.setAttribute('width', '26');
            svgIcon.innerHTML = '<path fill="#666666" d="M22,12a10.009,10.009,0,1,1-9-9.949v8.226a2,2,0,1,1-2,0V7.934A4.2,4.2,0,1,0,15,9.07V6.812a6,6,0,1,1-4-.722V4.069a7.993,7.993,0,1,0,4,.518V2.461A10.017,10.017,0,0,1,22,12Z"/>';
            svgIcon.style.cssText = "position: relative; top: 1px;";
            controlUI.appendChild(svgIcon);
        
            let isExpanded = false;
            let additionalControlDiv;
        
            controlUI.addEventListener('click', function() {
                toggleControls();
            });
            
            function toggleControls() {
                var mapContainer = document.querySelector('.cwc-map');
                var windowHeight = window.innerHeight;
                var expandedHeight = Math.max(windowHeight * 0.7, parseInt('250px', 10));
            
                if (!isExpanded) {
                    svgIcon.setAttribute('viewBox', '0 0 512 512');
                    svgIcon.innerHTML = '<path fill="#666666" d="M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z"/>';
                    mapContainer.style.height = `${expandedHeight}px`;
                    map.setZoom(6);
                    additionalControlDiv = createAdditionalControl();
                    controlDiv.appendChild(additionalControlDiv);
                    changeLayer('precipitation_new');
                    isExpanded = true;
                } else {
                    svgIcon.setAttribute('viewBox', '0 0 24 24');
                    svgIcon.innerHTML = '<path fill="#666666" d="M22,12a10.009,10.009,0,1,1-9-9.949v8.226a2,2,0,1,1-2,0V7.934A4.2,4.2,0,1,0,15,9.07V6.812a6,6,0,1,1-4-.722V4.069a7.993,7.993,0,1,0,4,.518V2.461A10.017,10.017,0,0,1,22,12Z"/>';
                    mapContainer.style.height = '32vh';
                    map.setZoom(8);
                    collapseControl(additionalControlDiv);
                    additionalControlDiv = null;
                    map.overlayMapTypes.clear();
                    isExpanded = false;
                }
            }
            
            function collapseControl(div) {
                div.firstChild.style.opacity = 0;
                div.lastChild.style.opacity = 0;
                setTimeout(() => {
                    div.style.height = '0px';
                    setTimeout(() => controlDiv.removeChild(div), 300);
                }, 300);
            }
        
            function createAdditionalControl() {
                let div = document.createElement('div');
                div.classList.add('cwc-mapcontrol');
                div.style.cssText = "height: 0px; background-color: #fff; border: 2px solid #fff; border-radius: 2px; box-shadow: 0 2px 6px rgba(0,0,0,.3); margin-left: 10px; font-family: 'misans', sans-serif; color: #000; overflow: hidden; transition: height 0.3s ease; box-sizing: border-box;";
                div.innerHTML = `<div style="opacity: 0; transition: opacity 0.3s ease 0.3s; padding: 10px 10px 0px 10px;"><span style="font-size: 18px;"><?php echo $lang['radar_map_title']; ?></span><br><span style="opacity: 0.8; font-size: 12px;"><?php echo $lang['radar_map_subtitle']; ?></span></div><div style="margin-top: 8px; opacity: 0; transition: opacity 0.3s ease 0.3s; padding: 0px 10px 10px 10px;"><label class="cwc-radio"><input type="radio" id="precipitation" name="layer" value="precipitation_new" checked><i class="cwc-radio-icon"></i><?php echo $lang['pop']; ?></label><label class="cwc-radio"><input type="radio" id="clouds" name="layer" value="clouds_new"><i class="cwc-radio-icon"></i><?php echo $lang['cld']; ?></label><label class="cwc-radio"><input type="radio" id="pressure" name="layer" value="pressure_new"><i class="cwc-radio-icon"></i><?php echo $lang['pressure']; ?></label><label class="cwc-radio"><input type="radio" id="wind" name="layer" value="wind_new"><i class="cwc-radio-icon"></i><?php echo $lang['wind']; ?></label><label class="cwc-radio"><input type="radio" id="temp" name="layer" value="temp_new"><i class="cwc-radio-icon"></i><?php echo $lang['temp']; ?></label><label class="cwc-radio"><input type="radio" id="none" name="layer" value="none"><i class="cwc-radio-icon"></i><?php echo $lang['hid']; ?></label></div>`;
                div.querySelectorAll('input[type="radio"][name="layer"]').forEach(radio => {
                    radio.addEventListener('click', function() {
                    if (this.value === 'none') {
                        map.overlayMapTypes.clear();
                        } else {
                            changeLayer(this.value);
                        }
                    });
                });
                document.body.appendChild(div);
                    const fullHeight = div.scrollHeight;
                    document.body.removeChild(div);
                
                    div.style.height = '0px';
                    setTimeout(() => {
                        div.style.height = fullHeight + "px";
                        div.firstChild.style.opacity = 1;
                        div.lastChild.style.opacity = 1;
                    }, 10);
                return div;
            }
            
            function changeLayer(layerType) {
                map.overlayMapTypes.clear();
                var weatherLayer = new google.maps.ImageMapType({
                    getTileUrl: function(coord, zoom) {
                        return `https://tile.openweathermap.org/map/${layerType}/${zoom}/${coord.x}/${coord.y}.png?appid=d84453a2afd414c3a3cf2e103369c046`;
                    },
                    tileSize: new google.maps.Size(256, 256)
                });
                map.overlayMapTypes.push(weatherLayer);
            }

            var initialMarker = new google.maps.Marker({
                position: location,
                map: map
            });
                
            var userMarker = null;
            var geocoder = new google.maps.Geocoder();
            function fetchWeatherData(lat, lon, callback) {
                var weatherApiUrl = 'https://weather.caner.center/external/weather_current.php/?lat=' + lat + '&lon=' + lon + '&lang=<?php echo $lang['lang_variant']; ?>&units=<?php echo $units; ?>';
                var xhr = new XMLHttpRequest();
                xhr.open('GET', weatherApiUrl, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        callback(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send();
            }
            map.addListener('click', function(e) {
                if (userMarker) {
                    userMarker.setMap(null);
                }
                userMarker = new google.maps.Marker({
                    position: e.latLng,
                    map: map
                });
                geocoder.geocode({'location': e.latLng}, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        var city = "<?php echo $lang['map_pin_place']; ?>";
                        var addressComponents = results[0].address_components;
                        for(var i = 0; i < addressComponents.length; i++) {
                            var types = addressComponents[i].types;
                            if(types.indexOf("locality") > -1) {
                                city = addressComponents[i].long_name;
                                break;
                            }
                        }
                        fetchWeatherData(e.latLng.lat(), e.latLng.lng(), function(weatherData) {
                            var contentString='<div class="cwc-maps-label cwc-maps-content">'+'<strong>'+city+' '+weatherData.weather[0].description+'</strong><br>'+'<img src="https://openweathermap.org/img/wn/'+weatherData.weather[0].icon+'@4x.png" class="cwc-maps-label-icon">'+'<?php echo $lang['temp']; ?>: '+weatherData.main.temp+'<?php echo getTemperatureUnit($units); ?><br>'+'<?php echo $lang['humidity']; ?>: '+weatherData.main.humidity+'%<br>'+'<?php echo $lang['wind']; ?>: '+weatherData.wind.speed+' <?php echo getWindSpeedUnit($units); ?> • '+weatherData.wind.deg+'°<br>'+'<span class="no-wrap"><?php echo $lang['visibility']; ?>: '+weatherData.visibility+' m<br><?php echo $lang['cloud']; ?>: '+weatherData.clouds.all+'%'+'</span></div>'+'<a href="https://weather.caner.center/?location='+e.latLng.lat().toFixed(6)+','+e.latLng.lng().toFixed(6)+'" class="cwc-maps-link"><?php echo $lang['check_detail_weather']; ?></a>&nbsp;&nbsp;'+'<span class="cwc-maps-span" onclick="copyTextToClipboard(\''+e.latLng.lat().toFixed(6)+','+e.latLng.lng().toFixed(6)+'\', this)"><?php echo $lang['copy_latlon']; ?></span>';
                            var infowindow = new google.maps.InfoWindow({
                                content: contentString
                            });
                            infowindow.open(map, userMarker);
                        });
                    }
                });
            });
            setMapStyle(map, window.matchMedia('(prefers-color-scheme: dark)').matches);
            window.matchMedia('(prefers-color-scheme: dark)').addListener(e => {
                setMapStyle(map, e.matches);
            });
        <?php endif; ?>
        mapLoaded = true;
        }
        
        function setMapStyle(map, darkMode) {
            var styles = darkMode ? [
                {elementType: "geometry", stylers: [{color: "#222B3A"}]},
                {elementType: "labels.text.stroke", stylers: [{color: "#1B2331"}]},
                {elementType: "labels.text.fill", stylers: [{color: "#FFFFFF"}]},
                {featureType: "administrative.locality", elementType: "labels.text.fill", stylers: [{color: "#FFFFFF"}]},
                {featureType: "poi", elementType: "labels.text.fill", stylers: [{color: "#d59563"}]},
                {featureType: "poi.park", elementType: "geometry", stylers: [{color: "#113238"}]},
                {featureType: "poi.park", elementType: "labels.text.fill", stylers: [{color: "#6b9a76"}]},
                {featureType: "road", elementType: "geometry", stylers: [{color: "#38414e"}]},
                {featureType: "road", elementType: "geometry.stroke", stylers: [{color: "#212a37"}]},
                {featureType: "road", elementType: "labels.text.fill", stylers: [{color: "#9ca5b3"}]},
                {featureType: "road.highway", elementType: "geometry", stylers: [{color: "#746855"}]},
                {featureType: "road.highway", elementType: "geometry.stroke", stylers: [{color: "#1f2835"}]},
                {featureType: "road.highway", elementType: "labels.text.fill", stylers: [{color: "#f3d19c"}]},
                {featureType: "transit", elementType: "geometry", stylers: [{color: "#2f3948"}]},
                {featureType: "transit.station", elementType: "labels.text.fill", stylers: [{color: "#d59563"}]},
                {featureType: "water", elementType: "geometry", stylers: [{color: "#06080c"}]},
                {featureType: "water", elementType: "labels.text.fill", stylers: [{color: "#515c6d"}]},
                {featureType: "water", elementType: "labels.text.stroke", stylers: [{color: "#06080c"}]}
            ] : [];
        
            map.setOptions({styles: styles});
        }
        setTimeout(function() {
            if (!mapLoaded) {
                var mapElement = document.getElementById('map');
                mapElement.innerHTML = '<h3 class="cwc-center"><?php echo $lang['cannot_connect_gmap']; ?><br><span style="font-size: 12px;"><?php echo getMapErrorDescription($countryCode); ?></span><span class="cwc-acc" onclick="location.reload();" style="cursor: pointer; color: #FFC107;">&#8635; <?php echo $lang['reload']; ?></span></h3>';
            }
        }, 8000);
        </script>
        <script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GMapKey; ?>&callback=initGMap"></script>
        
    <?php elseif ($maps === "AMap"): ?>
    
        <div class="cwc-map" id="amap"></div>
        <script>
        <?php if (isset($latitude) && isset($longitude)): ?>
        window.onload = function() {
            initAMap();
            };
        function initAMap() {
            var latitude = <?php echo json_encode($latitude); ?>;
            var longitude = <?php echo json_encode($longitude); ?>;
            var map = new AMap.Map('amap', {
                viewMode: '2D',
                zoom: 8,
                center: [longitude, latitude],
                mapStyle: 'amap://styles/normal'
            });
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                map.setMapStyle('amap://styles/grey');
            }
            window.matchMedia('(prefers-color-scheme: dark)').addListener(function(e) {
                map.setMapStyle(e.matches ? 'amap://styles/grey' : 'amap://styles/normal');
            });
            var initialMarker = new AMap.Marker({
                position: new AMap.LngLat(longitude, latitude),
                map: map
            });
            var userMarker;
            function fetchWeatherData(lat, lon, callback) {
                var weatherApiUrl = 'https://weather.caner.center/external/weather_current.php/?lat=' + lat + '&lon=' + lon + '&lang=<?php echo $lang['lang_variant']; ?>&units=<?php echo $units; ?>';
                var xhr = new XMLHttpRequest();
                xhr.open('GET', weatherApiUrl, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        callback(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send();
            }
            map.on('click', function(e) {
                if (userMarker) {
                    userMarker.setMap(null);
                }
                userMarker = new AMap.Marker({
                    position: e.lnglat,
                    map: map
                });
                fetchWeatherData(e.lnglat.getLat(), e.lnglat.getLng(), function(weatherData) {
                    var contentString = '<div class="cwc-maps-label cwc-maps-content">' +
                        '<strong><?php echo $lang['map_pin_place']; ?> ' + weatherData.weather[0].description + '</strong><br>' +
                        '<img src="https://openweathermap.org/img/wn/' + weatherData.weather[0].icon + '@4x.png" class="cwc-maps-label-icon">' +
                        '<?php echo $lang['temp']; ?>: ' + weatherData.main.temp + '<?php echo getTemperatureUnit($units); ?><br>' +
                        '<?php echo $lang['humidity']; ?>: ' + weatherData.main.humidity + '%<br>' +
                        '<?php echo $lang['wind']; ?>: ' + weatherData.wind.speed + ' <?php echo getWindSpeedUnit($units); ?> • ' + weatherData.wind.deg + '°<br>' +
                        '<?php echo $lang['visibility']; ?>: ' + weatherData.visibility + ' m<br><?php echo $lang['cloud']; ?>: ' + weatherData.clouds.all + '%' +
                        '</div>' +
                        '<a href="https://weather.caner.center/?location=' + e.lnglat.getLat().toFixed(6) + ',' + e.lnglat.getLng().toFixed(6) + '" class="cwc-maps-link"><?php echo $lang['check_detail_weather']; ?></a>&nbsp;&nbsp;' +
                        '<span class="cwc-maps-span" onclick="copyTextToClipboard(\'' + e.lnglat.getLat().toFixed(6) + ',' + e.lnglat.getLng().toFixed(6) + '\', this)"><?php echo $lang['copy_latlon']; ?></span>';
                    var infoWindow = new AMap.InfoWindow({
                        content: contentString,
                        offset: new AMap.Pixel(0, -30)
                    });
                    infoWindow.open(map, e.lnglat);
                });
            });
        }
        <?php endif; ?>
        </script>
        <script src="https://webapi.amap.com/maps?v=2.0&key=8ae43d6d5516543a52b4c17676b6e204&callback=initAMap"></script>
    
    <?php endif; ?>
    
        <div id="weather-alerts" class="<?php echo (isset($weatherDataOWM['alerts']) && !empty($weatherDataOWM['alerts'])) ? 'alert-active' : ''; ?>">
        <div class="<?php echo (isset($weatherDataOWM['alerts']) && !empty($weatherDataOWM['alerts'])) ? 'cwc-svg-alert' : 'cwc-svg-checked'; ?>"></div>
            <div class="cwc-alert-tip">
                <h3 class="cwc-acc"><?php echo $lang['weather_alert']; ?></h3>
                <?php if (isset($weatherDataOWM['alerts']) && !empty($weatherDataOWM['alerts'])): ?>
                    <?php foreach ($weatherDataOWM['alerts'] as $alert): ?>
                        <div class="alert-item">
                            <h3 class="cwc-acc"><?php echo $alert['event']; ?></h3>
                            <?php if(isset($alert['tags']) && is_array($alert['tags']) && count($alert['tags']) > 0): ?>
                            <p class="cwc-text-small cwc-acc"><?php echo $lang['alert_type']; ?>: <?php echo nl2br(implode(", ", $alert['tags'])); ?></p>
                        <?php endif; ?>
                            <p class="cwc-text-small cwc-wraptextr cwc-acc"><?php echo $lang['alert_sender']; ?>: <?php echo $alert['sender_name']; ?><br><?php echo $lang['alert_start']; ?>: <?php echo convertToLocalTime($alert['start'], $timezone); ?><br><?php echo $lang['alert_end']; ?>: <?php echo convertToLocalTime($alert['end'], $timezone); ?></p>
                            <p class="cwc-wraptextr cwc-acc"><?php echo nl2br($alert['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="cwc-acc cwc-noalerts"><?php echo $lang['alert_none']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    
    <div class="cwc-weather-container">
        <?php if (isset($weatherDataOWM['current'])): ?>
        <?php $current = $weatherDataOWM['current']; ?>
        <h3 class="cwc-acc"><?php echo $lang['weather_current']; ?> | <?php echo convertToLocalTime($current['dt'], $timezone); ?></h3>
                <div>
                <div class="cwc-acc">
                    <strong class="cwc-headline"><?php echo $current['temp']; ?></strong><strong><?php echo getTemperatureUnit($units); ?></strong>
                </div>
                    <img src="https://openweathermap.org/img/wn/<?php echo $current['weather'][0]['icon']; ?>@4x.png" alt="Weather Icon" style="float: right; height: 100px; margin-top: -70px;">
                
                    <strong class="cwc-subhead cwc-acc"><?php echo $current['weather'][0]['description']; ?></strong>
                    <br>
                    <span class="cwc-text-small cwc-acc no-wrap">
                        <?php echo $lang['feelslike']; ?> <strong><?php echo $current['feels_like']; ?><?php echo getTemperatureUnit($units); ?></strong> • <?php echo $lang['dew']; ?> <strong><?php echo $current['dew_point']; ?><?php echo getTemperatureUnit($units); ?></strong>
                    </span>
                </div>
            <hr class="cwc-special-hr">
            <p>
                <span class="cwc-headtip-n"><?php echo $lang['north']; ?></span>
                <svg class="totop-btn" id="windFlag" xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 512 512" style="margin-right: 17px; margin-top: 28px; float: right; transform: rotate(<?php echo $current['wind_deg']; ?>deg); transform-origin: center;">
                    <polyline points="112 244 256 100 400 244" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                    <line x1="256" y1="120" x2="256" y2="412" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                </svg>
            <div class="cwc-acc">
                <strong class="cwc-headline"><?php echo $current['wind_speed']; ?></strong><strong><?php echo getWindSpeedUnit($units); ?></strong>
            </div>
               
                <span class="cwc-subhead cwc-acc">
                    <strong><?php echo getWindSpeedDescription($current['wind_speed'], $units); ?> • <?php echo getWindDirection($current['wind_deg']); ?> (<?php echo $current['wind_deg']; ?>°)</strong>
                </span>
                <br>
                <span class="cwc-text-small cwc-acc">
                    <?php echo $lang['wind_gust']; ?>: <strong><?php echo isset($current['wind_gust']) ? $current['wind_gust'] . ' ' . getWindSpeedUnit($units) : $lang['nodata']; ?> • <?php echo isset($current['wind_gust']) ? getWindSpeedDescription($current['wind_gust'], $units) : $lang['nodata']; ?></strong>
                </span>
                <div class="cwc-progress-container cwc-acc" style="margin-top: 10px;">
                    <span class="cwc-progress-text cwc-acc"><?php echo $lang['nowind']; ?></span>
                    <div class="cwc-progress cwc-acc">
                        <?php
                        $maxWindSpeed = $units == 'imperial' ? 72.9 : 32.6;
                        $progressPercentage = ($current['wind_speed'] / $maxWindSpeed) * 100;
                        ?>
                        <div class="cwc-progress-determinate" style="width: <?php echo $progressPercentage; ?>%;"></div>
                        <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo $progressPercentage; ?>%;">
                            <polygon points="448 368 256 144 64 368 448 368" fill="#2196F3"/>
                        </svg>
                        <span class="arrow-text cwc-acc" style="left: calc(<?php echo $progressPercentage; ?>% + 10px);">
                            <?php echo getWindSpeedDescription($current['wind_speed'], $units); ?>
                        </span>
                    </div>
                    <span class="cwc-progress-text cwc-acc"><?php echo $lang['hurricane']; ?> (> <?php echo $maxWindSpeed; ?> <?php echo getWindSpeedUnit($units); ?>)</span>
                </div>
            </p>
            
            <hr class="cwc-special-2-hr">
            
            <div class="cwc-weather-icon-svg" style="margin-top: 8px;">
                <div class="cwc-svg-uvi"></div>
            </div>
            <div class="cwc-weather-icon-container">
            <p>
                <strong class="cwc-acc"><?php echo $lang['uvindex']; ?>: <?php echo sprintf($lang['uvi_level'], $current['uvi']); ?></strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc"><?php echo $lang['uvi_lv0']; ?></span>
                <div class="cwc-progress cwc-acc" style="background-color: #FFF8E1;">
                    <div class="cwc-progress-determinate" style="background-color: #FFC107; width: <?php echo ($current['uvi'] / 12) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo ($current['uvi'] / 12) * 100; ?>%;">
                        <polygon points="448 368 256 144 64 368 448 368" fill="#FFC107"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo ($current['uvi'] / 12) * 100; ?>% + 10px);">
                        <?php echo getUVIndexDescription($current['uvi']); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc"><?php echo $lang['uvi_lv12']; ?></span>
            </div>
            </div>
            
            <div class="cwc-weather-icon-svg" style="margin-top: 27px;">
                <div class="cwc-svg-visibility"></div>
            </div>
            <div class="cwc-weather-icon-container">
            <p class="cwc-top-20">
                <strong class="cwc-acc"><?php echo $lang['visibility']; ?>: <?php echo $current['visibility']; ?> m</strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc">0m</span>
                <div class="cwc-progress cwc-acc" style="background-color: #E0F2F1;">
                    <div class="cwc-progress-determinate" style="background-color: #009688; width: <?php echo ($current['visibility'] / 10000) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo ($current['visibility'] / 10000) * 100; ?>%;">
                        <polygon points="448 368 256 144 64 368 448 368" fill="#009688"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo ($current['visibility'] / 10000) * 100; ?>% + 10px);">
                        <?php echo getVisibilityDescription($current['visibility']); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc">10000m (<?php echo $lang['max']; ?>)</span>
            </div>
            </div>
            
            <div class="cwc-weather-icon-svg" style="margin-top: 27px;">
                <div class="cwc-svg-humidity"></div>
            </div>
            <div class="cwc-weather-icon-container">
            <p class="cwc-top-20">
                <strong class="cwc-acc"><?php echo $lang['humidity']; ?>: <?php echo $current['humidity']; ?>%</strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc">0%</span>
                <div class="cwc-progress cwc-acc" style="background-color: #E0F7FA;">
                    <div class="cwc-progress-determinate" style="background-color: #00BCD4; width: <?php echo ($current['humidity'] / 100) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo ($current['humidity'] / 100) * 100; ?>%;">
                        <polygon points="448 368 256 144 64 368 448 368" fill="#00BCD4"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo ($current['humidity'] / 100) * 100; ?>% + 10px);">
                        <?php echo getHumidityDescription($current['humidity']); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc">100% (<?php echo $lang['too_humid']; ?>)</span>
            </div>
            </div>
            
            <div class="cwc-weather-icon-svg" style="margin-top: 27px;">
                <div class="cwc-svg-clouds"></div>
            </div>
            <div class="cwc-weather-icon-container">
            <p class="cwc-top-20">
                <strong class="cwc-acc"><?php echo $lang['cloud']; ?>: <?php echo $current['clouds']; ?>%</strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc">0%</span>
                <div class="cwc-progress" style="background-color: #d4d4d4;">
                    <div class="cwc-progress-determinate" style="background-color: #525252; width: <?php echo ($current['clouds'] / 100) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo ($current['clouds'] / 100) * 100; ?>%;">
                        <polygon points="448 368 256 144 64 368 448 368" fill="#525252"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo ($current['clouds'] / 100) * 100; ?>% + 10px);">
                        <?php echo getCloudCoverageDescription($current['clouds']); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc">100% (<?php echo $lang['may_pop']; ?>)</span>
            </div>
            </div>
            
            <div class="cwc-weather-icon-svg" style="margin-top: 27px;">
                <div class="cwc-svg-pressure"></div>
            </div>
            <div class="cwc-weather-icon-container">
            <p class="cwc-top-20">
                <strong class="cwc-acc"><?php echo $lang['pressure']; ?>: <?php echo $current['pressure']; ?> hPa</strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc">950</span>
                <div class="cwc-progress cwc-acc" style="background-color: #E8EAF6;">
                    <div class="cwc-progress-determinate" style="background-color: #3F51B5; width: <?php echo (($current['pressure'] - 950) / 100) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo (($current['pressure'] - 950) / 100) * 100; ?>%;">
                        <polygon points="448 368 256 144 64 368 448 368" fill="#3F51B5"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo (($current['pressure'] - 950) / 100) * 100; ?>% + 10px);">
                        <?php echo getPressureDescription($current['pressure']); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc">1050hPa (<?php echo $lang['extreme']; ?>)</span>
            </div>
            </div>
            
            <hr class="cwc-hr">
            
            <p>
                <div class="cwc-acc"><?php echo sprintf($lang['aqi_level'], $aqi); ?></div>
                <strong class="cwc-subhead cwc-acc"><?php echo $lang['aqi_full']; ?>: <?php echo getAqiDescription($aqi); ?></strong><br>
            </p>
            <div class="cwc-progress-container cwc-acc">
                <span class="cwc-progress-text cwc-acc">0</span>
                <div class="cwc-progress cwc-acc" style="background-color: <?php echo $bgColor; ?>;">
                    <div class="cwc-progress-determinate" style="background-color: <?php echo $barColor; ?>; width: <?php echo ($aqi / 5) * 100; ?>%;"></div>
                    <svg class="progress-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" style="left: <?php echo ($aqi / 5) * 100; ?>%; fill: <?php echo $barColor; ?>;">
                        <polygon points="448 368 256 144 64 368 448 368"/>
                    </svg>
                    <span class="arrow-text cwc-acc" style="left: calc(<?php echo ($aqi / 5) * 100; ?>% + 10px);">
                        <?php echo getAqiDescription($aqi); ?>
                    </span>
                </div>
                <span class="cwc-progress-text cwc-acc"><?php echo $lang['aqi_lv5']; ?></span>
            </div>
            
    <div class="cwc-switch-container">
        <div id="aqi-content-1" class="aqi-content cwc-acc">
            <p class="cwc-weather-suggestion"><?php echo sprintf($lang['aqi_advice'], getAqiDescription($aqi)); ?>: <br><br><?php echo $displaySuggestion; ?></p>
        </div>
              
        <div id="aqi-content-2" class="aqi-content">
            <div class="cwc-flexbox-container">
                <div class="cwc-column cwc-column-aqi">
                    <p class="cwc-text-small cwc-acc"><?php echo $lang['plu_gas']; ?>：<br>
                        CO: <strong><?php echo $components['co']; ?> μg/m³ • <?php echo getPollutantDescription($components['co'], 'CO'); ?></strong><br>
                        NO<sub>2</sub>: <strong><?php echo $components['no2']; ?> μg/m³ • <?php echo getPollutantDescription($components['no2'], 'NO2'); ?></strong><br>
                        O<sub>3</sub>: <strong><?php echo $components['o3']; ?> μg/m³ • <?php echo getPollutantDescription($components['o3'], 'O3'); ?></strong><br>
                        SO<sub>2</sub>: <strong><?php echo $components['so2']; ?> μg/m³ • <?php echo getPollutantDescription($components['so2'], 'SO2'); ?></strong>
                    </p>
                </div>
                <div class="cwc-column">
                     <p class="cwc-text-small cwc-acc"><?php echo $lang['plu_particles']; ?>：<br>
                        PM2.5: <strong><?php echo $components['pm2_5']; ?> μg/m³ • <?php echo getPollutantDescription($components['pm2_5'], 'PM2.5'); ?></strong><br>
                        PM10: <strong><?php echo $components['pm10']; ?> μg/m³ • <?php echo getPollutantDescription($components['pm10'], 'PM10'); ?></strong>
                        <br><?php echo $lang['nonplu_gas']; ?>：<br>
                        NO: <strong><?php echo $components['no']; ?> μg/m³</strong><br>
                        NH<sub>3</sub>: <strong><?php echo $components['nh3']; ?> μg/m³</strong>
                    </p>
                </div>
            </div>
        </div>
    
    </div>
        <button id="aqi-content-switch" class="cwc-btn cwc-action-btn cwc-acc"><span id="button-text"></span></button>
        <?php endif; ?>
    </div>
    
    <div class="cwc-gpt">
        <h3 class="cwc-acc"><?php echo $lang['weather_advice_title']; ?></h3>
        <div id="adviceContent" class="cwc-acc"><p class="cwc-weather-suggestion gpt-suggestion-tips"><?php echo $lang['advice_tip_wait']; ?></p></div>
    </div>
    
    <div id="minutely-forecast">
        <h3 class="cwc-acc"><?php echo $lang['pop_chart_title']; ?></h3>
        <?php if (isset($weatherDataOWM['minutely']) && is_array($weatherDataOWM['minutely'])): ?>
            <canvas class="cwc-acc" aria-label="<?php echo $lang['pop_chart_arai']; ?>" id="precipitationChart"></canvas>
        <?php else: ?>
            <p class="cwc-acc"><?php echo $lang['pop_chart_nodata']; ?></p>
        <?php endif; ?>
    </div>
    <div id="hourly-weather-forecast-chart">
        <h3 id="changeTitle" class="cwc-acc"><span></span></h3>
            <canvas id="WeatherChart" aria-label="<?php echo $lang['forecast_charts_arai']; ?>" class="cwc-acc"></canvas>
        <button id="changeChart" class="cwc-btn cwc-action-btn cwc-acc"><span></span></button>
    </div>
    
    <div id="hourly-forecast">
        <h3 class="cwc-acc"><?php echo $lang['weather_48h_forecast']; ?></h3>
        <?php if (isset($weatherDataOWM['hourly']) && is_array($weatherDataOWM['hourly'])): ?>
        <div class="scrollable-container-hourly">
            <?php foreach ($weatherDataOWM['hourly'] as $hour): ?>
                <div class="hour-item">
                    <h4 class="cwc-acc"><?php echo $lang['time']; ?>: <?php echo convertToLocalHM($hour['dt'], $timezone); ?></h4>
                    <div class="cwc-acc">
                        <span class="cwc-text-small"><?php echo $lang['temp']; ?>: </span><br>
                        <strong class="cwc-head-hourly"><?php echo $hour['temp']; ?></strong>
                        <strong><?php echo getTemperatureUnit($units); ?></strong>
                        <img src="https://openweathermap.org/img/wn/<?php echo $hour['weather'][0]['icon']; ?>@4x.png" alt="Weather Icon" style="float: right; height: 70px; bottom: 5px; position: relative;"><br>
                        <span class="cwc-text-small">
                            <?php echo $lang['feelslike']; ?>: <strong><?php echo $hour['feels_like']; ?><?php echo getTemperatureUnit($units); ?></strong><br><?php echo $lang['dew']; ?>: <strong><?php echo $hour['dew_point']; ?><?php echo getTemperatureUnit($units); ?></strong><br>
                            <?php echo $lang['weather_status']; ?>: <strong><?php echo $hour['weather'][0]['description']; ?></strong><br>
                            <?php echo $lang['visibility']; ?>: <strong><?php echo $hour['visibility']; ?>m • <?php echo getVisibilityDescription($hour['visibility']); ?></strong><br>
                            <?php echo $lang['cloud']; ?>: <strong><?php echo $hour['clouds']; ?>% • <?php echo getCloudCoverageDescription($hour['clouds']); ?></strong><br>
                            <?php echo $lang['uvindex']; ?>: <strong><?php echo $hour['uvi']; ?> • <?php echo getUVIndexDescription($hour['uvi']); ?></strong>
                        </span>
                    </div>
                    <hr class="cwc-hr-dashed">
                    <div class="cwc-acc">
                        <span class="cwc-text-small"><?php echo $lang['pop_pr']; ?>: </span><br>
                        <strong class="cwc-head-hourly"><?php echo $hour['pop'] * 100; ?></strong><strong>%</strong><br>
                        <span class="cwc-text-small">
                        <?php echo $lang['humidity']; ?>: <strong><?php echo $hour['humidity']; ?>% • <?php echo getHumidityDescription($hour['humidity']); ?></strong><br>
                        <?php echo $lang['pressure']; ?>: <strong><?php echo $hour['pressure']; ?> hPa • <?php echo getPressureDescription($hour['pressure']); ?></strong>
                        </span>
                    </div>
                    <hr class="cwc-hr-dashed">
                    <div class="cwc-acc">
                        <span class="cwc-text-small"><?php echo $lang['wind_speed']; ?>: </span><br>
                        <strong class="cwc-head-hourly"><?php echo $hour['wind_speed']; ?></strong>
                        <strong><?php echo getWindSpeedUnit($units); ?></strong><br>
                        <span class="cwc-text-small">
                            <strong><?php echo getWindSpeedDescription($hour['wind_speed'], $units); ?></strong><br>
                            <?php echo $lang['wind_gust']; ?>: <strong><?php echo $hour['wind_gust']; ?> <?php echo getWindSpeedUnit($units); ?> • <?php echo getWindSpeedDescription($hour['wind_gust'], $units); ?></strong><br>
                            <?php echo $lang['wind_deg']; ?>: <strong><?php echo getWindDirection($hour['wind_deg']); ?> (<?php echo $hour['wind_deg']; ?>°)</strong><br>
                        </span>
                        <div class="cwc-vane-container">
                            <div class="wind-direction">
                                <span class="north-indicator"><?php echo $lang['north']; ?></span>
                                <svg id="windFlag" class="totop-btn" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 512 512" style="transform: rotate(<?php echo $hour['wind_deg']; ?>deg); transform-origin: center;">
                                    <polyline points="112 244 256 100 400 244" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                                    <line x1="256" y1="120" x2="256" y2="412" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="daily-forecast">
        <h3 class="cwc-acc"><?php echo $lang['weather_8d_forecast']; ?></h3>
        <?php if (isset($weatherDataOWM['daily']) && is_array($weatherDataOWM['daily'])): ?>
        <div class="scrollable-container-daily">
            <?php foreach ($weatherDataOWM['daily'] as $day): ?>
                <div class="day-item">
                    <h4 class="cwc-acc"><?php echo $lang['date']; ?>: <?php echo convertToLocalDate($day['dt'], $timezone); ?></h4>
                    <div class="cwc-acc">
                        <img src="https://openweathermap.org/img/wn/<?php echo $day['weather'][0]['icon']; ?>@4x.png" alt="Weather Icon" style="float: right; height: 70px; top: 20px; position: relative;">
                        <span class="cwc-text-small"><?php echo $lang['max_min_temp']; ?>: </span><br>
                        <span class="cwc-daily-arrow">&uarr;</span>
                        <strong class="cwc-head-daily"><?php echo $day['temp']['max']; ?></strong>
                        <strong><?php echo getTemperatureUnit($units); ?></strong><br>
                        <span class="cwc-daily-arrow">&darr;</span>
                        <strong class="cwc-head-daily"><?php echo $day['temp']['min']; ?></strong>
                        <strong><?php echo getTemperatureUnit($units); ?></strong>
                        <br>
                        <span class="cwc-text-small"><?php echo $lang['temp_timeline']; ?>: </span><br>
                        <span class="cwc-text-small">
                            <?php echo $lang['temp_morn']; ?>: <strong><?php echo $day['temp']['morn']; ?><?php echo getTemperatureUnit($units); ?></strong> • <strong><?php echo $day['feels_like']['morn']; ?><?php echo getTemperatureUnit($units); ?></strong><br>
                            <?php echo $lang['temp_day']; ?>: <strong><?php echo $day['temp']['day']; ?><?php echo getTemperatureUnit($units); ?></strong> • <strong><?php echo $day['feels_like']['day']; ?><?php echo getTemperatureUnit($units); ?></strong><br>
                            <?php echo $lang['temp_eve']; ?>: <strong><?php echo $day['temp']['eve']; ?><?php echo getTemperatureUnit($units); ?></strong> • <strong><?php echo $day['feels_like']['eve']; ?><?php echo getTemperatureUnit($units); ?></strong><br>
                            <?php echo $lang['temp_night']; ?>: <strong><?php echo $day['temp']['night']; ?><?php echo getTemperatureUnit($units); ?></strong> • <strong><?php echo $day['feels_like']['night']; ?><?php echo getTemperatureUnit($units); ?></strong>
                        </span><br>
                        <span class="cwc-text-small"><?php echo $lang['dew']; ?>: <strong><?php echo $day['dew_point']; ?><?php echo getTemperatureUnit($units); ?></strong></span><br>
                    </div>
                    <div class="cwc-summary-container cwc-acc">
                        <strong class="cwc-summary"><?php echo $day['summary']; ?></strong><br>
                    </div>
                    <div class="cwc-acc">
                        <span class="cwc-subhead"><strong><?php echo getMoonPhaseDescription($day['moon_phase']); ?></strong></span><br>
                        <img src="https://resource.caner.hk/get/moon/icon/<?php echo getMoonPhaseIcon($day['moon_phase']); ?>.png" alt="Moon Phase" style="float: right; right: 13px; bottom: 10px; height: 37px; position: relative;">
                        <span class="cwc-text-small"><?php echo $lang['sunrise']; ?>: <strong><?php echo convertToLocalHM($day['sunrise'], $timezone); ?></strong><?php echo $lang['sunset']; ?>: <strong><?php echo convertToLocalHM($day['sunset'], $timezone); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['moonrise']; ?>: <strong><?php echo convertToLocalHM($day['moonrise'], $timezone); ?></strong><?php echo $lang['moonset']; ?>: <strong><?php echo convertToLocalHM($day['moonset'], $timezone); ?></strong></span><br>
                    </div>
                    <hr class="cwc-hr-dashed">
                    <div class="cwc-acc">
                        <span class="cwc-text-small"><?php echo $lang['pop_pr']; ?>: </span><br><strong class="cwc-head-daily"><?php echo $day['pop'] * 100; ?></strong><strong>%</strong><br>
                        <?php if (isset($day['rain'])): ?>
                            <div><span class="cwc-text-small"><?php echo $lang['rain']; ?>: <strong><?php echo $day['rain']; ?> mm</strong></span></div>
                        <?php endif; ?>
                        <?php if (isset($day['snow'])): ?>
                            <div><span class="cwc-text-small"><?php echo $lang['snow']; ?>: <strong><?php echo $day['snow']; ?> mm</strong></span></div>
                        <?php endif; ?>
                        <span class="cwc-text-small"><?php echo $lang['humidity']; ?>: <strong><?php echo $day['humidity']; ?>% • <?php echo getHumidityDescription($day['humidity']); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['pressure']; ?>: <strong><?php echo $day['pressure']; ?> hPa • <?php echo getPressureDescription($day['pressure']); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['cloud']; ?>: <strong><?php echo $day['clouds']; ?>% • <?php echo getCloudCoverageDescription($day['clouds']); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['uvindex']; ?>: <strong><?php echo $day['uvi']; ?> • <?php echo getUVIndexDescription($day['uvi']); ?></strong></span><br>
                    </div>
                    <hr class="cwc-hr-dashed">
                    <div class="cwc-acc">
                        <span class="cwc-text-small"><?php echo $lang['wind_speed']; ?>: </span><br><strong class="cwc-head-daily"><?php echo $day['wind_speed']; ?></strong><strong> <?php echo getWindSpeedUnit($units); ?></strong><br>
                        <span class="cwc-text-small"><strong><?php echo getWindSpeedDescription($day['wind_speed'], $units); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['wind_gust']; ?>: <strong><?php echo isset($day['wind_gust']) ? $day['wind_gust'] . ' ' . getWindSpeedUnit($units) : 'N/A'; ?> • <?php echo getWindSpeedDescription(isset($day['wind_gust']) ? $day['wind_gust'] : 0, $units); ?></strong></span><br>
                        <span class="cwc-text-small"><?php echo $lang['wind_deg']; ?>: <strong><?php echo getWindDirection($day['wind_deg']); ?> (<?php echo $day['wind_deg']; ?>°)</strong></span><br>
                        <div class="cwc-vane-container">
                            <div class="wind-direction">
                                <span class="north-indicator"><?php echo $lang['north']; ?></span>
                                <svg id="windFlag" class="totop-btn" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 512 512" style="transform: rotate(<?php echo $day['wind_deg']; ?>deg); transform-origin: center;">
                                    <polyline points="112 244 256 100 400 244" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                                    <line x1="256" y1="120" x2="256" y2="412" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
        <?php if (isset($_GET['location']) && !empty($_GET['location'])): ?>
        
        <div class="cwc-result-err-container">
            <div class="cwc-svg-error"></div>
            <div class="cwc-alert-tip">
            <h3 class="cwc-acc"><?php echo sprintf($lang['weather_unavailable'], htmlspecialchars($_GET['location'])); ?></h3>
            <div class="cwc-text-tips cwc-acc"><?php echo $lang['unavailable_tip']; ?></div>
            </div>
        </div>
        
        <div class="cwc-margin-10 cwc-break-word">
            <hr class="cwc-errorpage-top-hr">
            <?php if (isset($errorMessage)): ?>
                <span class="cwc-text-small cwc-acc">
                    <?php echo $lang['provider_error_tip']; ?><br>
                </span>
                
                <div class="cwc-typo">
                    <blockquote class="cwc-acc">
                        <p><strong><?php echo $lang['diagnosis']; ?>: <br><span id="ServiceError"><?php echo $errorMessage; ?></span></strong></p>
                    </blockquote>
                    <blockquote class="cwc-acc">
                        <strong>
                            <p><?php echo $lang['solution']; ?>:<br>
                            &rarr; <?php echo $lang['solution_tip']; ?>
                        </strong>
                    </blockquote>
                </div>
                <strong class="cwc-acc"><?php echo $lang['help_tip']; ?></strong>
                <a href="mailto:support@caner.hk?subject=CWC 出现严重问题&body=你好，Caner 支持人员，目前 Caner Weather Channel 无法返回任何天气数据，根据页面提示，后台诊断信息为：%0D%0A%0D%0A[<?php echo $errorMessage; ?>]%0D%0A%0D%0A请尽快修复此问题。感谢您的支持！" class="cwc-btn cwc-acc" style="margin-top: 15px;"><?php echo $lang['connect_caner']; ?></a>
                <hr class="cwc-errorpage-hr">
                <strong class="cwc-acc"><?php echo $lang['ask_wait']; ?></strong>
            </div>
            <iframe id="gameFrame" src="https://resource.caner.hk/get/game/dino/dino_with_title.html" height="200px" frameborder="0" scrolling="no" allowfullscreen class="cwc-game cwc-acc" aria-label="<?php echo $lang['dino_game_arai']; ?>"></iframe>
            <button id="gameButton" class="cwc-btn cwc-acc" style="margin-bottom: 15px;"><?php echo $lang['dino_game_btn']; ?></button>
            <?php else: ?>
                <span class="cwc-text-small cwc-acc">
                    <?php echo $lang['no_ocean_tip']; ?><br>
                </span>
                <div class="cwc-typo">
                    <blockquote class="cwc-acc">
                        <p><strong><?php echo $lang['diagnosis']; ?>: <br><span id="ServiceError"><?php echo sprintf($lang['location_error'], htmlspecialchars($_GET['location'])); ?></span></strong></p>
                    </blockquote>
                    <blockquote class="cwc-acc">
                        <strong>
                            <p><?php echo $lang['solution']; ?>:<br>
                            &rarr; <?php echo $lang['solution_1']; ?><br>
                            &rarr; <?php echo $lang['solution_2']; ?><br>
                            &larr; <a class="cwc-link" href="#" onclick="window.history.back(); return false;"><?php echo $lang['solution_3']; ?></a><br>
                            &rarr; <a class="cwc-link" href="https://weather.caner.center"><?php echo $lang['solution_4']; ?></a></p>
                        </strong>
                    </blockquote>
                </div>
                <hr class="cwc-errorpage-hr">
                <div class="cwc-acc" style="margin-bottom: 15px;"><strong><?php echo $lang['hot_weather_tip']; ?></strong></div>
                <div id="btn-group"></div>
                <hr class="cwc-errorpage-hr">
                <strong class="cwc-acc"><?php echo $lang['ask_connect']; ?></strong>
            </div>
            <iframe id="gameFrame" src="https://resource.caner.hk/get/game/dino/dino_with_title.html" height="200px" frameborder="0" scrolling="no" allowfullscreen class="cwc-game cwc-acc" aria-label="<?php echo $lang['dino_game_arai']; ?>"></iframe>
            <button id="gameButton" class="cwc-btn cwc-acc"><?php echo $lang['dino_game_btn']; ?></button>
            <a href="mailto:support@caner.hk" class="cwc-btn cwc-acc error-page-btn"><?php echo $lang['connect_caner']; ?></a>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const baseHref = "https://weather.caner.center/?location=";
                    const locations = [
                        { query: "伦敦", text: "英国 伦敦" },
                        { query: "曼彻斯特", text: "英国 曼彻斯特" },
                        { query: "伯明翰", text: "英国 伯明翰" },
                        { query: "爱丁堡", text: "英国 爱丁堡" },
                        { query: "利物浦", text: "英国 利物浦" },
                        { query: "洛杉矶", text: "美国 洛杉矶" },
                        { query: "纽约", text: "美国 纽约" },
                        { query: "芝加哥", text: "美国 芝加哥" },
                        { query: "旧金山", text: "美国 旧金山" },
                        { query: "北京市", text: "中国 北京市" },
                        { query: "上海市", text: "中国 上海市" },
                        { query: "棉兰", text: "印度尼西亚 棉兰" },
                        { query: "塞维利亚", text: "西班牙 塞维利亚" },
                        { query: "基督城", text: "新西兰 基督城" },
                        { query: "达尼丁", text: "新西兰 达尼丁" },
                        { query: "汉密尔顿", text: "新西兰 汉密尔顿" },
                        { query: "雅典", text: "希腊 雅典" },
                        { query: "塞萨洛尼基", text: "希腊 塞萨洛尼基" },
                        { query: "帕特雷", text: "希腊 帕特雷" },
                        { query: "伊拉克利翁", text: "希腊 伊拉克利翁" },
                        { query: "拉里萨", text: "希腊 拉里萨" },
                        { query: "都柏林", text: "爱尔兰 都柏林" },
                        { query: "科克", text: "爱尔兰 科克" },
                        { query: "利默里克", text: "爱尔兰 利默里克" },
                        { query: "加尔韦", text: "爱尔兰 加尔韦" },
                        { query: "沃特福德", text: "爱尔兰 沃特福德" },
                        { query: "台中", text: "台湾 台中" },
                        { query: "台南", text: "台湾 台南" },
                        { query: "新竹", text: "台湾 新竹" },
                        { query: "新加坡", text: "新加坡 新加坡" }
                    ];
                    
                    function getRandomLocations(n) {
                        const shuffled = locations.sort(() => 0.5 - Math.random());
                        return shuffled.slice(0, n);
                    }
                    
                    function displayButtons() {
                        const btnGroup = document.getElementById('btn-group');
                        btnGroup.innerHTML = '';
                        const selectedLocations = getRandomLocations(4);
                        
                        selectedLocations.forEach(location => {
                            const link = document.createElement('a');
                            link.href = `${baseHref}${encodeURIComponent(location.query)}`;
                            link.className = 'cwc-btn cwc-acc';
                            link.textContent = location.text;
                            btnGroup.appendChild(link);
                        });
                    }
                    displayButtons();
                });
            </script>
            <?php endif; ?>
                <script>
                    document.getElementById('gameButton').addEventListener('click', function() {
                        var gameFrame = document.getElementById('gameFrame');
                        var gameButton = document.getElementById('gameButton');
                    
                        if (gameFrame.style.maxHeight === '0px') {
                            gameFrame.style.maxHeight = '200px';
                            setTimeout(function() {
                                gameFrame.style.opacity = '1';
                            }, 500);
                            gameButton.textContent = '<?php echo $lang['dino_game-close']; ?>';
                        } else {
                            gameFrame.style.opacity = '0';
                            setTimeout(function() {
                                gameFrame.style.maxHeight = '0px';
                            }, 500);
                            gameButton.textContent = '<?php echo $lang['dino_game_btn']; ?>';
                        }
                    });
                </script>
        <?php endif; ?>
    <?php endif; ?>
    
    </main>
    <footer class="footer">
        <svg aria-label="Caner Weather Channel 的 LOGO" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 512 512" style="position: relative; top: 10px; margin-leftt: 10px; margin-bottom: 15px;" class="cwc-svg cwc-acc"><path d="M340,480H106c-29.5,0-54.92-7.83-73.53-22.64C11.23,440.44,0,415.35,0,384.8c0-26.66,10.08-49.8,29.14-66.91,15.24-13.68,36.17-23.21,59-26.84h0c.06,0,.08,0,.09-.05,6.44-39,23.83-72.09,50.31-95.68A140.24,140.24,0,0,1,232,160c30.23,0,58.48,9.39,81.71,27.17a142.69,142.69,0,0,1,45.36,60.66c29.41,4.82,54.72,17.11,73.19,35.54C453,304.11,464,331.71,464,363.2c0,32.85-13.13,62.87-37,84.52C404.11,468.54,373.2,480,340,480Zm19-232.18Z"/><path d="M381.5,219.89a169.23,169.23,0,0,1,45.44,19A96,96,0,0,0,281,129.33q-2.85,2-5.54,4.2a162.47,162.47,0,0,1,57.73,28.23A174.53,174.53,0,0,1,381.5,219.89Z"/><rect x="448" y="192" width="64" height="32"/><rect x="320" y="32" width="32" height="64"/><path d="M255.35,129.63l12.45-12.45L223.18,72.55,200.55,95.18l33.17,33.17h.6A172,172,0,0,1,255.35,129.63Z"/><rect x="406.27" y="90.18" width="63.11" height="32" transform="translate(53.16 340.68) rotate(-45)"/></svg><br>
        <strong class="footer-head cwc-acc c-w-c">Caner Weather Channel</strong>
        <div style="margin-top: 8px;">
        <div class="cwc-typo">
        <blockquote class="cwc-acc">
        <span class="cwc-text-small"><?php echo $lang['site_description']; ?></span>
        </blockquote>
        </div>
        </div>
        <hr class="cwc-hr-double footer-border">
        <div class="follow-icon-container">
        <a href="https://x.com/CanerCente88952?t=w5n9qhcfgPVdRcx48NAibw&s=09" target="_blank" aria-label="前往Caner Twitter的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M496,109.5a201.8,201.8,0,0,1-56.55,15.3,97.51,97.51,0,0,0,43.33-53.6,197.74,197.74,0,0,1-62.56,23.5A99.14,99.14,0,0,0,348.31,64c-54.42,0-98.46,43.4-98.46,96.9a93.21,93.21,0,0,0,2.54,22.1,280.7,280.7,0,0,1-203-101.3A95.69,95.69,0,0,0,36,130.4C36,164,53.53,193.7,80,211.1A97.5,97.5,0,0,1,35.22,199v1.2c0,47,34,86.1,79,95a100.76,100.76,0,0,1-25.94,3.4,94.38,94.38,0,0,1-18.51-1.8c12.51,38.5,48.92,66.5,92.05,67.3A199.59,199.59,0,0,1,39.5,405.6,203,203,0,0,1,16,404.2,278.68,278.68,0,0,0,166.74,448c181.36,0,280.44-147.7,280.44-275.8,0-4.2-.11-8.4-.31-12.5A198.48,198.48,0,0,0,496,109.5Z"/></svg></a>
        <a href="https://www.facebook.com/profile.php?id=61556338823487&mibextid=ZbWKwL" target="_blank" aria-label="前往Caner Facebook的图标按钮"><svg xmlns="http://www.w3.org/2000/svg"  width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M480,257.35c0-123.7-100.3-224-224-224s-224,100.3-224,224c0,111.8,81.9,204.47,189,221.29V322.12H164.11V257.35H221V208c0-56.13,33.45-87.16,84.61-87.16,24.51,0,50.15,4.38,50.15,4.38v55.13H327.5c-27.81,0-36.51,17.26-36.51,35v42h62.12l-9.92,64.77H291V478.66C398.1,461.85,480,369.18,480,257.35Z" fill-rule="evenodd"/></svg></a>
        <a href="https://github.com/Caner-HK/CWC-Caner-Weather-Channel/" target="_blank" aria-label="前往CWC GitHub项目的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M256,32C132.3,32,32,134.9,32,261.7c0,101.5,64.2,187.5,153.2,217.9a17.56,17.56,0,0,0,3.8.4c8.3,0,11.5-6.1,11.5-11.4,0-5.5-.2-19.9-.3-39.1a102.4,102.4,0,0,1-22.6,2.7c-43.1,0-52.9-33.5-52.9-33.5-10.2-26.5-24.9-33.6-24.9-33.6-19.5-13.7-.1-14.1,1.4-14.1h.1c22.5,2,34.3,23.8,34.3,23.8,11.2,19.6,26.2,25.1,39.6,25.1a63,63,0,0,0,25.6-6c2-14.8,7.8-24.9,14.2-30.7-49.7-5.8-102-25.5-102-113.5,0-25.1,8.7-45.6,23-61.6-2.3-5.8-10-29.2,2.2-60.8a18.64,18.64,0,0,1,5-.5c8.1,0,26.4,3.1,56.6,24.1a208.21,208.21,0,0,1,112.2,0c30.2-21,48.5-24.1,56.6-24.1a18.64,18.64,0,0,1,5,.5c12.2,31.6,4.5,55,2.2,60.8,14.3,16.1,23,36.6,23,61.6,0,88.2-52.4,107.6-102.3,113.3,8,7.1,15.2,21.1,15.2,42.5,0,30.7-.3,55.5-.3,63,0,5.4,3.1,11.5,11.4,11.5a19.35,19.35,0,0,0,4-.4C415.9,449.2,480,363.1,480,261.7,480,134.9,379.7,32,256,32Z"/></svg></a>
        <a href="https://youtube.com/@CanerHK?si=2mNUf_XQtMukCIQI" target="_blank" aria-label="前往Caner YouTube频道的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M508.64,148.79c0-45-33.1-81.2-74-81.2C379.24,65,322.74,64,265,64H247c-57.6,0-114.2,1-169.6,3.6-40.8,0-73.9,36.4-73.9,81.4C1,184.59-.06,220.19,0,255.79q-.15,53.4,3.4,106.9c0,45,33.1,81.5,73.9,81.5,58.2,2.7,117.9,3.9,178.6,3.8q91.2.3,178.6-3.8c40.9,0,74-36.5,74-81.5,2.4-35.7,3.5-71.3,3.4-107Q512.24,202.29,508.64,148.79ZM207,353.89V157.39l145,98.2Z"/></svg></a>
        <a href="mailto:connect@caner.hk" target="_blank" aria-label="邮件联系Caner的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M424,80H88a56.06,56.06,0,0,0-56,56V376a56.06,56.06,0,0,0,56,56H424a56.06,56.06,0,0,0,56-56V136A56.06,56.06,0,0,0,424,80Zm-14.18,92.63-144,112a16,16,0,0,1-19.64,0l-144-112a16,16,0,1,1,19.64-25.26L256,251.73,390.18,147.37a16,16,0,0,1,19.64,25.26Z"/></svg></a>
        </div>
        <a href="https://weather.caner.center/cookies/" class="cwc-footer-link link-space">Manage CWC Cookies</a>
        <a href="https://status.caner.center/" class="cwc-footer-link link-space">Status</a>
        <a href="https://donate.caner.hk/" class="cwc-footer-link">Donate</a>
        <br><br>
        <a href="#top" class="cwc-footer-top">Back To Top &uarr;</a>
        <hr class="cwc-hr-solid footer-border">
        <div class="cwc-typo">
        <blockquote class="cwc-acc">
        <strong>CWC service provider</strong><br><span style="font-size: 14px;"><strong>CWC is the abbreviation of Caner Weather Channel</strong><br><a href="https://openweathermap.org/" target="_blank" class="cwc-footer-link-noafter">Open Weather</a> is a service provider of CWC and provides weather data, <a href="https://caner.hk/" target="_blank" class="cwc-footer-link-noafter">Caner HK</a> built the page you are using based on the One Call API and the Professional Collection API<br>Data analysis, translations, maps, and search suggestions within CWC pages are powered by <a href="https://cloud.google.com" target="_blank" class="cwc-footer-link-noafter">Google Cloud</ a> Provide support<br><a href="https://openai.com/" target="_blank" class="cwc-footer-link-noafter">OpenAI</a> GPT-4 Turbo AI model provides clothing and travel recommendations for CWC
        </blockquote>
        </div>
        <hr class="cwc-hr-double footer-border">
        <div style="margin-bottom: 15px; margin-top: -5px;">
        <img class="cwc-acc" aria-label="Caner的LOGO" style="height: 42px;" id="caner-logo" src="https://resource.caner.hk/get/logo/caner_logo_black.png"><br>
        <span class="cwc-text-small cwc-acc">CWC is a project which was designed and built by Caner HK.</span><br>
        <span class="cwc-text-small cwc-acc">This webpage is only for OpenWeather Challenge.</span><br>
        <span class="cwc-text-small cwc-acc">Special Version 1.0.0 Updated on 2024/5/12.</span><br>
        <span class="cwc-text-small cwc-acc">&copy;&nbsp;Caner&nbsp;HK&nbsp;<span id="year"></span> - All Rights Reserved.</span>
        </div>
    </footer>
    <script defer src="https://resource.caner.hk/get/chart_js/chart.js"></script>
    <script>
    function getLocation() {
        if (navigator.geolocation) {
            var options = {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            };
            navigator.geolocation.getCurrentPosition(showPosition, showError, options);
        } else {
            console.log("Geolocation is not supported by this browser.");
        }
    }

    function showPosition(position) {
        var latitude = position.coords.latitude.toFixed(6);
        var longitude = position.coords.longitude.toFixed(6);
        window.location.href = "https://weather.caner.center/?location=" + latitude + "," + longitude;
    }

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                console.log("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                console.log("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                console.log("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                console.log("An unknown error occurred.");
                break;
        }
    }

    document.getElementById('getLocation').addEventListener('click', function() {
        var button = this;
        var svg = button.querySelector('svg');
        var path = svg.querySelector('path');
        button.style.animation = 'breathe-effect 0.8s ease-in-out infinite';
        getLocation();

        setTimeout(function() {
            button.style.animation = 'shrink 0.3s ease-in-out forwards';

            setTimeout(function() {
                path.setAttribute('d', "M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z");
                button.style.animation = 'grow 0.3s ease-in-out forwards';
                button.disabled = true;
            }, 300);
        }, 8000);
    });

    window.addEventListener('load', function() {
        var currentLocation = window.location.search;
        if (!currentLocation.includes("location=")) {
            getLocation();
        }
    });
    
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + encodeURIComponent(name).replace(/[\-\.\+\*]/g, '\\$&') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }
    
    var cookieData = getCookie('CWC-Profile') ? JSON.parse(getCookie('CWC-Profile')) : null;
    
    document.getElementById('location').addEventListener('focus', function() {
        var suggestions = document.getElementById('suggestions');
        var closeBtn = document.getElementById('closeBtn');
        <?php if ($autocomplete !== "browser"): ?>
            if (cookieData && cookieData.History && cookieData.History.length > 0) {
                displayHistory(cookieData.History, suggestions);
                closeBtn.style.display = 'block';
            }
        <?php endif; ?>
    });
    
    document.getElementById('location').addEventListener('keyup', function() {
        var input = this.value;
        var suggestions = document.getElementById('suggestions');
        var closeBtn = document.getElementById('closeBtn');
        clearTimeout(this.delay);
    
        if (input.length >= 1) {
            suggestions.innerHTML = '';
            this.delay = setTimeout(function() {
                closeBtn.style.display = 'block';
                fetch(`./external/search_suggestions.php?input=${encodeURIComponent(input)}&lang=<?php echo $lang['lang']; ?>`)
                    .then(response => response.json())
                    .then(data => {
                        data.predictions.forEach(function(prediction) {
                            var li = document.createElement('li');
                            li.textContent = prediction.description;
                            li.addEventListener('click', function() {
                                document.getElementById('location').value = prediction.description;
                                suggestions.style.opacity = '0';
                                suggestions.style.transform = 'scaleY(0)';
                                setTimeout(function() {
                                    suggestions.style.display = 'none';
                                }, 300);
                                closeBtn.style.display = 'none';
                            });
                            suggestions.appendChild(li);
                        });
                        if (suggestions.childElementCount > 0) {
                            suggestions.style.display = 'block';
                            setTimeout(function() {
                                suggestions.style.opacity = '1';
                                suggestions.style.transform = 'scaleY(1)';
                            }, 10);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, 300);
        } 
        <?php if ($autocomplete !== "browser"): ?>
            else if (cookieData && cookieData.History && cookieData.History.length > 0) {
                displayHistory(cookieData.History, suggestions);
                closeBtn.style.display = 'block';
            }
        <?php endif; ?>
    });

    <?php if ($autocomplete !== "browser"): ?>
    
    function updateIcon(container, newIcon) {
        container.innerHTML = newIcon;
    }
    
    function displayHistory(history, container) {
        container.innerHTML = '';
        var cookieData = getCookie('CWC-Profile') ? JSON.parse(getCookie('CWC-Profile')) : {};
    
        history.forEach(function(entry, index) {
            var li = document.createElement('li');
            li.classList.add('cwc-history-item');
    
            var iconSpan = document.createElement('span');
            iconSpan.classList.add('cwc-history-icon');
            iconSpan.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/></svg>';
            li.appendChild(iconSpan);
    
            var textContainer = document.createElement('div');
            textContainer.classList.add('fade-text');
            var textSpan = document.createElement('span');
            textSpan.textContent = entry.place;
            textContainer.appendChild(textSpan);
            li.appendChild(textContainer);
    
            let autoFillShown = false;
            let deletePromptShown = false;
    
            li.addEventListener('click', function() {
                if (!deletePromptShown && !autoFillShown) {
                    document.getElementById('location').value = entry.place;
                    autoFillShown = true;
                    fadeOutAndIn(textContainer, '<?php echo $lang['autocompleted']; ?>', '<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="44" d="M416 128L192 384l-96-96"/></svg>', function() {
                        setTimeout(function() {
                            fadeOutAndIn(textContainer, entry.place, '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/></svg>', function() {
                                autoFillShown = false;
                            });
                        }, 2000);
                    });
                }
            });
    
            li.addEventListener('contextmenu', function(event) {
                if (!deletePromptShown) {
                    event.preventDefault();
                    deletePromptShown = true;
                    this.classList.add('context-menu-active');
                    fadeOutAndIn(textContainer, '<?php echo $lang['ask_click_del']; ?>', '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" width="24px" height="24px"><path d="M 10 2 L 9 3 L 4 3 L 4 5 L 5 5 L 5 20 C 5 20.522222 5.1913289 21.05461 5.5683594 21.431641 C 5.9453899 21.808671 6.4777778 22 7 22 L 17 22 C 17.522222 22 18.05461 21.808671 18.431641 21.431641 C 18.808671 21.05461 19 20.522222 19 20 L 19 5 L 20 5 L 20 3 L 15 3 L 14 2 L 10 2 z M 7 5 L 17 5 L 17 20 L 7 20 L 7 5 z M 9 7 L 9 18 L 11 18 L 11 7 L 9 7 z M 13 7 L 13 18 L 15 18 L 15 7 L 13 7 z"/></svg>', function() {
                        li.addEventListener('click', function() {
                            if (deletePromptShown) {
                                cookieData.History.splice(index, 1);
                                updateCookieHistory(cookieData);
                                li.remove();
                            }
                            deletePromptShown = false;
                            this.classList.remove('context-menu-active');
                        }, { once: true });
                    });
                }
            });
    
            container.prepend(li);
        });
    
        if (container.childElementCount > 0) {
            container.style.display = 'block';
            setTimeout(function() {
                container.style.opacity = '1';
                container.style.transform = 'scaleY(1)';
            }, 10);
        }
    }
    
    function fadeOutAndIn(element, newText, newIconHTML, callback) {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.3s ease';
    
        var iconContainer = element.parentElement.querySelector('.cwc-history-icon');
        iconContainer.style.opacity = '0';
        iconContainer.style.transition = 'opacity 0.3s ease';
    
        element.addEventListener('transitionend', function handler() {
            element.removeEventListener('transitionend', handler);
    
            element.innerHTML = '';
            var textSpan = document.createElement('span');
            textSpan.textContent = newText;
            element.appendChild(textSpan);
            element.style.opacity = '1';
    
            if (newIconHTML) {
                iconContainer.innerHTML = newIconHTML;
                iconContainer.style.opacity = '1';
            }
    
            if (callback && typeof callback === 'function') {
                callback();
            }
        }, { once: true });
    }

    function updateCookieHistory(cookieData) {
        var expirationTime = new Date(cookieData.Expiration);
        document.cookie = "CWC-Profile=" + encodeURIComponent(JSON.stringify(cookieData)) + "; path=/; expires=" + expirationTime.toUTCString();
    }

    <?php endif; ?>
    
    document.getElementById('closeBtn').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('location').value = '';
        document.getElementById('suggestions').style.opacity = '0';
        document.getElementById('suggestions').style.transform = 'scaleY(0)';
        setTimeout(function() {
            document.getElementById('suggestions').style.display = 'none';
        }, 300);
        this.style.display = 'none';
    });
    
    window.addEventListener('scroll', function() {
        var header = document.querySelector('header');
        if (window.scrollY > 0) {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                header.style.boxShadow = '0px 4px 2px -2px rgba(255,255,255,0.5)';
            } else {
                header.style.boxShadow = '0px 4px 2px -2px gray';
            }
        } else {
            header.style.boxShadow = 'none';
        }
    });
    
    function hideElement(element, callback) {
        element.style.opacity = 0; 
        setTimeout(() => {
            element.style.display = 'none';
            if (callback) callback();
        }, 300);
    }
    
    function showElement(element) {
        element.style.display = 'block';
        setTimeout(() => element.style.opacity = 1, 10);
    }
    
    <?php if (isset($current)): ?>
    
    var weatherData = <?php echo json_encode($weatherDataOWM); ?>;
    var forecastAqiData = <?php echo json_encode($forecastAqiData); ?>;
    
    document.addEventListener("DOMContentLoaded", function() {
        if (weatherData !== null && weatherData.minutely !== undefined) {
            var minutelyData = weatherData.minutely;
            var timezoneOffset = weatherData.timezone_offset;
    
            const precipitationLabels = minutelyData.map(item => new Date((item.dt + timezoneOffset) * 1000).toISOString().substr(11, 5));
            const precipitationData = minutelyData.map(item => item.precipitation);
    
            const precipitationConfig = {
                type: 'bar',
                data: {
                    labels: precipitationLabels,
                    datasets: [{
                        label: '<?php echo $lang['precipitation']; ?> (mm/h)',
                        data: precipitationData,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };
    
            const precipitationCtx = document.getElementById('precipitationChart').getContext('2d');
            const precipitationChart = new Chart(precipitationCtx, precipitationConfig);
        }
        
        var hourlyData = weatherData.hourly;
        var timezoneOffset = weatherData.timezone_offset;
        const labels = hourlyData.map(hour => new Date((hour.dt + timezoneOffset) * 1000).toISOString().substr(11, 5)); 
    
        var dataTypes = [
            {
                keys: ['temp', 'feels_like', 'dew_point'], 
                labels: ['<?php echo $lang['temp']; ?> (<?php echo getTemperatureUnit($units); ?>)', '<?php echo $lang['feelslike']; ?> (<?php echo getTemperatureUnit($units); ?>)', '<?php echo $lang['dew']; ?> (<?php echo getTemperatureUnit($units); ?>)'], 
                title: '<?php echo $lang['temp_chart_title']; ?>', 
                colors: ['#FF6384', '#36A2EB', '#4BC0C0'], 
                next: '<?php echo $lang['temp_chart_next']; ?>'
            },
            {
                keys: ['pop', 'humidity'], 
                labels: ['<?php echo $lang['pop_pr']; ?> (%)', '<?php echo $lang['humidity']; ?> (%)'], 
                title: '<?php echo $lang['wet_chart_title']; ?>', 
                colors: ['#03A9F4', '#00BCD4'], 
                next: '<?php echo $lang['wet_chart_next']; ?>'
            },
            {
                keys: ['wind_speed', 'wind_gust'], 
                labels: ['<?php echo $lang['wind_speed']; ?> (<?php echo getWindSpeedUnit($units); ?>)', '<?php echo $lang['wind_gust']; ?> (<?php echo getWindSpeedUnit($units); ?>)'], 
                title: '<?php echo $lang['wind_chart_title']; ?>', 
                colors: ['#64B5F6', '#4DD0E1'], 
                next: '<?php echo $lang['visibility']; ?>'
            },
            {
                keys: ['visibility'], 
                labels: ['<?php echo $lang['visibility']; ?> (m)'], 
                title: '<?php echo $lang['vision_chart_title']; ?>', 
                colors: ['#00BFA5'], 
                next: '<?php echo $lang['uvindex']; ?>'
            },
            {
                keys: ['uvi'], 
                labels: ['<?php echo $lang['uvindex']; ?> (<?php echo $lang['level']; ?>)'], 
                title: '<?php echo $lang['uvi_chart_title']; ?>', 
                colors: ['#FFD740'], 
                next: '<?php echo $lang['pressure']; ?>'
            },
            {
                keys: ['pressure'], 
                labels: ['<?php echo $lang['pressure']; ?> (hPa)'], 
                title: '<?php echo $lang['pre_chart_title']; ?>', 
                colors: ['#2979FF'], 
                next: '<?php echo $lang['cloud']; ?>'
            },
            {
                keys: ['clouds'], 
                labels: ['<?php echo $lang['cloud']; ?> (%)'], 
                title: '<?php echo $lang['cld_chart_title']; ?>', 
                colors: ['#546E7A'], 
                next: '<?php echo $lang['aqi']; ?>'
            },
            {
                keys: ['aqi'],
                labels: ['<?php echo $lang['aqi']; ?> (<?php echo $lang['level']; ?>)'],
                title: '<?php echo $lang['aqi_chart_title']; ?>',
                colors: ['#8E8CD8'],
                next: '<?php echo $lang['temp']; ?>'
            }
        ];
        var currentDataTypeIndex = 0;
    
        var ctx = document.getElementById('WeatherChart').getContext('2d');
        var weatherChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        
        function updateChart() {
            var dataType = dataTypes[currentDataTypeIndex];
        
            if (dataType.keys[0] === 'aqi' && forecastAqiData && forecastAqiData.list) {
                weatherChart.data.labels = forecastAqiData.list.map(item => {
                    const date = new Date(item.dt * 1000);
                    return `${date.getMonth() + 1}/${date.getDate()}`;
                });
        
                weatherChart.data.datasets = [{
                    label: dataType.labels[0],
                    backgroundColor: dataType.colors[0] + '33',
                    borderColor: dataType.colors[0],
                    data: forecastAqiData.list.map(item => item.main.aqi),
                    pointRadius: 1,
                    fill: false
                }];
            } else {
                weatherChart.data.labels = hourlyData.map(hour => new Date((hour.dt + timezoneOffset) * 1000).toISOString().substr(11, 5));
                weatherChart.data.datasets = dataType.keys.map((key, index) => {
                    return {
                        label: dataType.labels[index],
                        backgroundColor: dataType.colors[index] + '33',
                        borderColor: dataType.colors[index],
                        data: hourlyData.map(hour => key === 'pop' ? hour[key] * 100 : hour[key]),
                        pointRadius: 1,
                        fill: false
                    };
                });
            }
            weatherChart.update();
        
            let chartTitle = document.getElementById('changeTitle').querySelector('span');
            let chartButton = document.getElementById('changeChart').querySelector('span');
            hideElement(chartTitle, () => {
                chartTitle.textContent = dataType.title + '<?php echo $lang['forecast']; ?>';
                showElement(chartTitle);
            });
            hideElement(chartButton, () => {
                chartButton.textContent = '<?php echo $lang['change_to']; ?>' + dataType.next + '<?php echo $lang['chart']; ?>';
                showElement(chartButton);
            });
        }
            
        document.getElementById('changeChart').addEventListener('click', function() {
            currentDataTypeIndex = (currentDataTypeIndex + 1) % dataTypes.length;
            updateChart();
        });
        updateChart();

    });
    
    <?php endif; ?>
    
    document.querySelector('.header-btn').addEventListener('click', function() {
        this.style.transform = this.style.transform === 'rotate(180deg)' ? 'rotate(0deg)' : 'rotate(180deg)';
    });
    
    document.getElementById('menuBtn').addEventListener('click', function() {
        var menu = document.querySelector('.cwc-menu');
        if (menu.style.marginTop === '0px') {
            menu.style.marginTop = '-449px';
        } else {
            menu.style.marginTop = '0px';
        }
    
        var title = document.getElementById('cwc-title');
        title.classList.add('fade-out');
        setTimeout(function() {
            if (title.innerHTML.includes("<?php echo $lang['settings']; ?>")) {
                title.innerHTML = '<a href="https://weather.caner.center" style="text-decoration: none; color: inherit; visited: inherit;"><span class="hide-s">Caner </span>Weather<span class="hide-xs"> Channel</span></a>';
            } else {
                title.innerHTML = '<a href="https://weather.caner.center" style="text-decoration: none; color: inherit; visited: inherit; position: relative; top: -2px; font-size: 22px;">CWC <span class="hide-s"><?php echo $lang['weather']; ?></span><?php echo $lang['settings']; ?></a>';
            }
            title.classList.remove('fade-out');
            title.classList.add('fade-in');
    
            setTimeout(function() {
                title.classList.remove('fade-in');
            }, 250);
        }, 250);
    });
    
    var isMenuBtnVisible = true;
    function fadeIn(element) {
        element.style.display = 'block';
        element.classList.remove('fade-out');
        element.classList.add('fade-in');
    }
    
    function fadeOut(element) {
        element.classList.remove('fade-in');
        element.classList.add('fade-out');
        element.addEventListener('animationend', function() {
            if (element.classList.contains('fade-out')) {
                element.style.display = 'none';
            }
        }, { once: true });
    }
    
    document.addEventListener('scroll', function() {
        var menuBtn = document.getElementById('menuBtn');
        var backtopBtn = document.getElementById('backtopBtn');
        if (window.scrollY > 350 && isMenuBtnVisible) {
            fadeOut(menuBtn);
            fadeIn(backtopBtn);
            isMenuBtnVisible = false;
        } else if (window.scrollY <= 350 && !isMenuBtnVisible) {
            fadeOut(backtopBtn);
            fadeIn(menuBtn);
            isMenuBtnVisible = true;
        }
    });
    
    document.getElementById('year').textContent = new Date().getFullYear();
    
    document.getElementById('backtopBtn').addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    document.querySelector('.cwc-from').addEventListener('submit', function(event) {
        var input = document.getElementById('location');
        var btn = document.getElementById('btn');
        if (!input.value.trim()) {
            event.preventDefault();
            input.classList.add('shake');
            btn.classList.add('shake');
            var originalPlaceholder = input.placeholder;
            input.placeholder = '<?php echo $lang['need_input_place']; ?>';
            setTimeout(function() {
                input.placeholder = originalPlaceholder;
            }, 3000);
            setTimeout(function() {
                input.classList.remove('shake');
                btn.classList.remove('shake');
            }, 500);
        }
    });
    
    document.getElementById('langForm').addEventListener('submit', function(event) {
        const formElement = this;
        const selectionValueElement = formElement.querySelector('.selection-value');
        const selectionValueText = selectionValueElement.textContent;
        if (selectionValueText === '<?php echo $lang['choose_lang_region']; ?>' || selectionValueText === '<?php echo $lang['need_lang_region']; ?>') {
            event.preventDefault(); 
            const customUnitsElement = formElement.querySelector('.cwc-horizontal-container');
            customUnitsElement.classList.add('shake');
            if (selectionValueText !== '<?php echo $lang['need_lang_region']; ?>') {
                selectionValueElement.textContent = '<?php echo $lang['need_lang_region']; ?>';
            }
            setTimeout(function() {
                customUnitsElement.classList.remove('shake');
                selectionValueElement.textContent = '<?php echo $lang['choose_lang_region']; ?>';
            }, 1500);
        }
    });
    
    document.getElementById('unitsForm').addEventListener('submit', function(event) {
        const formElement = this;
        const selectionValueElement = formElement.querySelector('.selection-value');
        const selectionValueText = selectionValueElement.textContent;
        if (selectionValueText === '<?php echo $lang['choose_weather_units']; ?>' || selectionValueText === '<?php echo $lang['need_units']; ?>') {
            event.preventDefault(); 
            const customUnitsElement = formElement.querySelector('.cwc-horizontal-container');
            customUnitsElement.classList.add('shake');
            if (selectionValueText !== '<?php echo $lang['need_units']; ?>') {
                selectionValueElement.textContent = '<?php echo $lang['need_units']; ?>';
            }
            setTimeout(function() {
                customUnitsElement.classList.remove('shake');
                selectionValueElement.textContent = '<?php echo $lang['choose_weather_units']; ?>';
            }, 1500);
        }
    });
    
    document.getElementById('mapsForm').addEventListener('submit', function(event) {
        const formElement = this;
        const selectionValueElement = formElement.querySelector('.selection-value');
        const selectionValueText = selectionValueElement.textContent;
        if (selectionValueText === '<?php echo $lang['choose_maps_provider']; ?>' || selectionValueText === '<?php echo $lang['need_map_provider']; ?>') {
            event.preventDefault();
            formElement.querySelector('.cwc-horizontal-container').classList.add('shake');
            if (selectionValueText !== '<?php echo $lang['need_map_provider']; ?>') {
                selectionValueElement.textContent = '<?php echo $lang['need_map_provider']; ?>';
            }
            setTimeout(function() {
                formElement.querySelector('.cwc-horizontal-container').classList.remove('shake');
                 selectionValueElement.textContent = '<?php echo $lang['choose_maps_provider']; ?>';
            }, 1500);
        }
    });
    
    document.querySelectorAll('.selection-value').forEach(function(selectionValue) {
        selectionValue.addEventListener('click', function() {
            const optionsContainer = this.nextElementSibling;
            if (optionsContainer.style.transform === 'scaleY(1)') {
                optionsContainer.style.opacity = '0';
                optionsContainer.style.transform = 'scaleY(0)';
                setTimeout(function() {
                    optionsContainer.style.display = 'none';
                }, 300);
            } else {
                optionsContainer.style.display = 'block';
                setTimeout(function() {
                    optionsContainer.style.opacity = '1';
                    optionsContainer.style.transform = 'scaleY(1)';
                }, 10);
            }
        });
    });
    
    document.querySelectorAll('.cwc-option').forEach(function(option) {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            this.parentNode.previousElementSibling.textContent = this.textContent;
            const optionsContainer = this.closest('.cwc-options');
            optionsContainer.style.opacity = '0';
            optionsContainer.style.top = '46px';
            setTimeout(function() {
                optionsContainer.style.display = 'none';
            }, 300);
            this.closest('.cwc-horizontal-container').querySelector('input[type="hidden"]').value = value;
        });
    });
    
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.cwc-selection').forEach(function(selection) {
            if (!selection.contains(e.target)) {
                const optionsContainer = selection.querySelector('.cwc-options');
                if(optionsContainer) {
                    optionsContainer.style.opacity = '0';
                    optionsContainer.style.top = '46px';
                    setTimeout(function() {
                        optionsContainer.style.display = 'none';
                    }, 300);
                }
            }
        });
    });
    
    window.addEventListener('load', () => {
        const titleText = document.getElementById('cookies-title-text');
        const originalText = titleText.textContent;
        const newText = '<?php echo $lang['cookies_title_new']; ?>';
        setTimeout(() => {
            titleText.classList.add('cookies-title-fade-out');
            setTimeout(() => {
                titleText.textContent = newText;
                titleText.classList.replace('cookies-title-fade-out', 'cookies-title-fade-in');
                setTimeout(() => {
                    titleText.classList.replace('cookies-title-fade-in', 'cookies-title-fade-out');
                    setTimeout(() => {
                        titleText.textContent = originalText;
                        titleText.classList.replace('cookies-title-fade-out', 'cookies-title-fade-in');
                    }, 500);
                }, 2000);
            }, 500);
        }, 2000);
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        var locationInput = document.getElementById('location');
        if (locationInput) {
            locationInput.addEventListener('click', function(event) {
                event.preventDefault();
            });
        }
        
        var logo = document.getElementById('caner-logo');
        var matchMedia = window.matchMedia('(prefers-color-scheme: dark)');
        function updateLogo() {
            if (matchMedia.matches) {
                logo.src = 'https://resource.caner.hk/get/logo/caner-logo-white.png';
            } else {
                logo.src = 'https://resource.caner.hk/get/logo/caner-logo-black.png';
            }
        }
        matchMedia.addListener(updateLogo);
        updateLogo();
        
        var cwcCookies = document.getElementById('cwc-cookies');
        var closeButton = document.getElementById('cookies-close');
        var cookieData = getCookie('CWC-Profile');
        var shouldShowCookiesDialog = true;
    
        if (cookieData) {
            var cookieObj = JSON.parse(cookieData);
            shouldShowCookiesDialog = !cookieObj.Confirm;
        }
    
        if (shouldShowCookiesDialog) {
            setTimeout(function() {
                cwcCookies.style.display = 'block';
                setTimeout(function() {
                    cwcCookies.style.marginTop = '0';
                }, 10);
            }, 800);
        }
        
        function generateUserID(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
        
        closeButton.addEventListener('click', function() {
            var now = new Date();
            var expirationTime = new Date(now.getTime() + 90*24*60*60*1000);
            var cookieObj = getCookie('CWC-Profile') ? JSON.parse(getCookie('CWC-Profile')) : {};
        
            cookieObj["User ID"] = generateUserID(16);
        
            cookieObj["Name"] = "CWC-Profile";
            cookieObj["Creation"] = now.toISOString();
            cookieObj["Expiration"] = expirationTime.toISOString();
            cookieObj["Confirm"] = "The user confirms CWC Cookies policy terms.";
            cookieObj["Google Analytics"] = "true";
            document.cookie = "CWC-Profile=" + encodeURIComponent(JSON.stringify(cookieObj)) + "; path=/; expires=" + expirationTime.toUTCString();
            
            cwcCookies.style.marginTop = 'calc(-100% + 16px)';
            setTimeout(function() {
                cwcCookies.style.display = 'none';
            }, 510);
        });
        
        <?php if (!empty($locationName)): ?>
        
        var visitDate = new Date().toISOString();
        var historyRecord = {
            date: visitDate,
            place: "<?php echo $locationName; ?>",
            latlon: "<?php echo (isset($latitude) && isset($longitude)) ? $latitude . ',' . $longitude : 'null'; ?>"
        };
        
        var cwcProfile = getCookie("CWC-Profile");
        var profileData = cwcProfile ? JSON.parse(cwcProfile) : { "Total Visit Count": 0, "History": [] };
        
        profileData["Total Visit Count"] += 1;
        if (profileData.History.length >= 3) {
            profileData.History.shift();
        }
        historyRecord.count = profileData["Total Visit Count"];
        profileData.History.push(historyRecord); 
        var expirationDate = new Date();
        expirationDate.setTime(expirationDate.getTime() + (90*24*60*60*1000));
        profileData.Expiration = expirationDate.toISOString();
        
        setCookie("CWC-Profile", JSON.stringify(profileData), 90);
        
        <?php endif; ?>
        
        function formatVisitDate(date) {
            var yyyy = date.getFullYear();
            var mm = ('0' + (date.getMonth() + 1)).slice(-2);
            var dd = ('0' + date.getDate()).slice(-2);
            var hh = ('0' + date.getHours()).slice(-2);
            var min = ('0' + date.getMinutes()).slice(-2);
            return yyyy + '-' + mm + '-' + dd + ' ' + hh + ':' + min;
        }
        
        <?php if (isset($current)): ?>
        
        var contents = document.querySelectorAll('.aqi-content');
        var switchButton = document.getElementById('aqi-content-switch');
        var currentIndex = 0;
        
        function hideElement(element, callback) {
            element.style.opacity = 0;
            setTimeout(() => {
                element.style.display = 'none';
                if (callback) callback();
            }, 300);
        }
        
        function showElement(element) {
            element.style.display = 'block';
            setTimeout(() => {
                element.style.opacity = 1;
            }, 10);
        }
        
        function updateButtonText(index) {
            const buttonTexts = ['<?php echo $lang['show_plu_detail']; ?>', '<?php echo $lang['show_aqi_advice']; ?>'];
            const buttonText = document.getElementById('button-text');
            buttonText.style.opacity = 0;
        
            setTimeout(() => {
                buttonText.textContent = buttonTexts[index];
                buttonText.style.opacity = 1;
            }, 300);
        }
        
        function switchContent() {
            updateButtonText((currentIndex + 1) % contents.length);
        
            var currentContent = contents[currentIndex];
            hideElement(currentContent, () => {
                currentIndex = (currentIndex + 1) % contents.length;
                var nextContent = contents[currentIndex];
                showElement(nextContent);
            });
        }
        
        contents.forEach((content, index) => {
            if (index === currentIndex) {
                showElement(content);
            } else {
                content.style.display = 'none';
                content.style.opacity = 0;
            }
        });
        updateButtonText(currentIndex);
        switchButton.addEventListener('click', switchContent);
        
        <?php endif; ?>
    
    });
    
    function calculateDaysSince(startDate) {
        var start = new Date(startDate);
        var now = new Date();
        var difference = now - start;
        var days = Math.floor(difference / (1000 * 60 * 60 * 24));
        return days;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var copyButtons = document.querySelectorAll('.cwc-maps-span');
        copyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var text = this.getAttribute('data-clipboard-text');
            copyTextToClipboard(text, this);
        });
      });
    });
    
    function copyTextToClipboard(text, element) {
        navigator.clipboard.writeText(text).then(function() {
            element.classList.add('fade-out');
            element.addEventListener('animationend', () => {
                element.innerHTML = '<?php echo $lang['copied']; ?>✓';
                element.classList.remove('fade-out');
                element.classList.add('fade-in');
    
                setTimeout(() => {
                    element.classList.remove('fade-in');
                    element.classList.add('fade-out');
                    element.addEventListener('animationend', () => {
                        element.innerHTML = '<?php echo $lang['copy_latlon']; ?>';
                        element.classList.remove('fade-out');
                        element.classList.add('fade-in');
                    }, { once: true });
                }, 1500);
            }, { once: true });
        }).catch(function(err) {
            console.error('Copy error', err);
            element.classList.add('fade-out');
            element.addEventListener('animationend', () => {
                element.innerHTML = '<?php echo $lang['copy_err']; ?>✖';
                element.classList.remove('fade-out');
                element.classList.add('fade-in');
            }, { once: true });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var accCheckbox = document.getElementById('acc-checkbox');
        if (accCheckbox.checked) {
            enableAccessibility();
        } else {
            disableAccessibility();
        }

        accCheckbox.addEventListener('change', function(event) {
            if (event.target.checked) {
                enableAccessibility();
                setCookie('Accessibility', 'true', 90, true);
            } else {
                disableAccessibility();
                setCookie('Accessibility', 'false', 90, true);
            }
        });
    });

    function enableAccessibility() {
        var elements = document.querySelectorAll('.cwc-acc, a, button');
        elements.forEach(function(element) {
            element.setAttribute('tabindex', '0');
            element.classList.add('cwc-acc-style');
        });
        document.addEventListener('focus', ttsFocusHandler, true);
    }

    function disableAccessibility() {
        var elements = document.querySelectorAll('.cwc-acc, a, button');
        elements.forEach(function(element) {
            element.removeAttribute('tabindex');
            element.classList.remove('cwc-acc-style');
        });
        document.removeEventListener('focus', ttsFocusHandler, true);
    }

    function ttsFocusHandler(e) {
        if (e.target.classList.contains('cwc-acc-style')) {
            var textToSpeak = "";
            if (e.target.tagName.toLowerCase() === 'a') {
                textToSpeak = "<?php echo $lang['link']; ?>：" + (e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label'));
            } else if (e.target.tagName.toLowerCase() === 'button') {
                textToSpeak = "<?php echo $lang['btn']; ?>：" + (e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label'));
            } else {
                textToSpeak = e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label');
            }
            if (textToSpeak) {
                if (typeof AndroidTTS !== 'undefined') {
                    AndroidTTS.speak(textToSpeak);
                } else if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    var msg = new SpeechSynthesisUtterance(textToSpeak);
                    window.speechSynthesis.speak(msg);
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var apoCheckbox = document.getElementById('apo-checkbox');
        apoCheckbox.addEventListener('change', function(event) {
            var autocompleteValue = event.target.checked ? "browser" : "cwc";
            updateAutocompleteSetting(autocompleteValue);

            var elementToAnimate = document.getElementById('needReload');
            if (elementToAnimate) {
                elementToAnimate.classList.add('fadeIn');
            }
        });
    });

    function updateAutocompleteSetting(value) {
        setCookie('Autocomplete', value, 90, true); 
    }

    function setCookie(name, value, expireDays, isProfile) {
        var d = new Date();
        if (expireDays) {
            d.setTime(d.getTime() + (expireDays * 24 * 60 * 60 * 1000));
        } else {
            d.setTime(d.getTime() + (90 * 24 * 60 * 60 * 1000));
        }
        var expires = "expires=" + d.toUTCString();

        if (isProfile) {
            var currentCookie = getCookie("CWC-Profile");
            var cookieData;
            if (currentCookie) {
                cookieData = JSON.parse(currentCookie);
            } else {
                cookieData = {};
            }
            cookieData[name] = value;
            cookieData["Expiration"] = d.toISOString();
            document.cookie = "CWC-Profile=" + encodeURIComponent(JSON.stringify(cookieData)) + ";" + expires + ";path=/";
        } else {
            document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
        }
    }

    <?php if (isset($current)): ?>

        document.getElementById('shareWeather').addEventListener('click', async () => {
            const shareSvg = document.getElementById('shareSvg');
            const shareTxt = document.getElementById('shareTxt');

            shareSvg.style.animationName = 'scaleDown';
            shareTxt.style.animationName = 'fadeOut';
            setTimeout(async () => {
                try {
                    await navigator.share({
                        title: '当前<?php echo htmlspecialchars($locationName); ?>的天气状况',
                        text: '当前<?php echo htmlspecialchars($locationName); ?>的天气：\n============\n<?php echo $current['weather'][0]['description']; ?>\n温度 <?php echo $current['temp']; ?><?php echo getTemperatureUnit($units); ?>，体感温度 <?php echo $current['feels_like']; ?><?php echo getTemperatureUnit($units); ?>\n<?php echo getWindDirection($current['wind_deg']); ?> <?php echo $current['wind_speed']; ?><?php echo getWindSpeedUnit($units); ?>，<?php echo getWindSpeedDescription($current['wind_speed'], $units); ?>\n紫外线<?php echo $current['uvi']; ?>级，<?php echo getUVIndexDescription($current['uvi']); ?>\n能见度<?php echo getVisibilityDescription($current['visibility']); ?>\n湿度<?php echo getHumidityDescription($current['humidity']); ?>\n空气质量<?php echo getAqiDescription($aqi); ?>\n============\n点击👇即可前往 CWC 查看全部天气信息',
                        url: 'https://cwc.caner.hk/?location=<?php echo $latitude; ?>,<?php echo $longitude; ?>'
                    });
                    shareSvg.innerHTML = '<path fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="44" d="M416 128L192 384l-96-96"/>';
                    shareTxt.textContent = '<?php echo $lang['share_successed']; ?>';
                } catch (error) {
                    if (error.name === 'AbortError') {
                        shareSvg.innerHTML = '<path d="M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z"/>';
                        shareTxt.textContent = '<?php echo $lang['share_canceled']; ?>';
                    } else {
                        shareSvg.innerHTML = '<path fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="32" d="M240 80l8 240h16l8-240h-32zM240 400h32v32h-32z"/>';
                        shareTxt.textContent = '<?php echo $lang['share_error']; ?>';
                    }
                }
                shareSvg.style.animationName = 'scaleUp';
                shareTxt.style.animationName = 'fadeIn';
            }, 250);
        });

    //GPT AJAX
        var weatherLocation = "<?php echo $locationName; ?>";
        var tempUnits = "<?php echo getTemperatureUnit($units); ?>";
        var windUnits = "<?php echo getWindSpeedUnit($units); ?>";
        var currentWeather = <?php echo json_encode($current); ?>;
        var hourlyForecasts = <?php echo json_encode(array_slice($weatherDataOWM['hourly'], 0, 12)); ?>; 
        var weatherAlertEvent = "<?php echo in_array($GCountryCode, ['CN', 'HK', 'MO', 'TW']) ? (!empty($alert['title']) ? $alert['title'] : $lang['alert_none'] ) : (isset($alert['event']) ? $alert['event'] : $lang['alert_none'] ); ?>";
        var weatherAQI = "<?php echo getAqiDescription($aqi); ?>, <?php echo $aqi; ?>"
        
        document.addEventListener('DOMContentLoaded', function() {
            fetchAdvice();
            function fetchAdvice() {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', './external/gpt_advice.php?lang=<?php echo $selected_lang; ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var adviceContent = document.getElementById('adviceContent');
                        adviceContent.classList.add('fade-out-gpt');
                        setTimeout(function() {
                            adviceContent.innerHTML = xhr.responseText;
                            adviceContent.classList.remove('fade-out-gpt');
                            adviceContent.classList.add('fade-in-gpt');
                        }, 300);
                    } else {
                        document.getElementById('adviceContent').innerHTML = "<p class='cwc-weather-suggestion gpt-suggestion-tips'><?php echo $lang['gpt_advice_unavaliable']; ?></p>";
                    }
                };
                xhr.onerror = function() {
                    document.getElementById('adviceContent').innerHTML = "<p class='cwc-weather-suggestion gpt-suggestion-tips'><?php echo $lang['gpt_advice_err']; ?></p>";
                };
                var dataToSend = JSON.stringify({
                    currentWeather: currentWeather,
                    hourlyForecasts: hourlyForecasts,
                    weatherLocation: weatherLocation,
                    windUnits: windUnits,
                    tempUnits: tempUnits,
                    weatherAlert: weatherAlertEvent,
                    weatherAQI: weatherAQI
                });
                xhr.send(dataToSend);
                
                setTimeout(function() {
                    var adviceContent = document.getElementById('adviceContent');
                    adviceContent.classList.add('fade-out-gpt');
                    setTimeout(function() {
                        adviceContent.classList.remove('fade-out-gpt');
                        adviceContent.classList.add('fade-in-gpt');
                        adviceContent.innerHTML = "<p class='cwc-weather-suggestion gpt-suggestion-tips'><?php echo $lang['advice_await_again']; ?></p>";
                        adviceContent.classList.remove('fade-in-gpt');
                    }, 350);
                }, 10000);
            }
        });
        
        //Translate AJAX
        <?php if ($lang['lang'] != 'en-US'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.cwc-summary').forEach(function(element) {
                var textToTranslate = element.textContent;
                var targetLanguage = '<?php echo $lang['lang']; ?>';
        
                fetch('./external/g_translate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'text=' + encodeURIComponent(textToTranslate) + '&target=' + encodeURIComponent(targetLanguage)
                })
                .then(response => response.text())
                .then(data => {
                    element.textContent = data;
                })
                .catch(error => console.error('Error:', error));
            });
        });
        <?php endif; ?>
    <?php endif; ?>
    </script>
</body>
</html>