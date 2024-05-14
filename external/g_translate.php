<?php
require '../access/fetch/curl_post.php';

$TodayDay = date('d');

if ($TodayDay < 15) {
    require '../access/api/config.php';
} else {
    require '../access/api/reserve.php';
}

if(isset($_POST['text'])) {
    $textToTranslate = $_POST['text'];
    $apiKey = Google_API;
    $url = 'https://translation.googleapis.com/language/translate/v2';
    $targetLanguage = isset($_POST['target']) ? $_POST['target'] : 'zh-CN';

    $data = [
        'q' => $textToTranslate,
        'source' => 'en',
        'target' => $targetLanguage,
        'format' => 'text',
        'key' => $apiKey,
    ];

    $response = curlPost($url, $data);
    $responseData = json_decode($response, true);
    
    if (!empty($responseData['data']['translations'][0]['translatedText'])) {
        echo $responseData['data']['translations'][0]['translatedText'];
    } else {
        echo $textToTranslate;
    }
}