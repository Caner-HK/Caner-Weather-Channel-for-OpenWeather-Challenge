<?php
$language = $_GET['lang'] ?? 'en-us';

switch ($language) {
    case 'zh-cn':
        echo "<p class='cwc-weather-suggestion'>由 GPT-4 Turbo AI 提供的天气建议暂时停止服务</p>";
        break;
    case 'zh-hk':
        echo "<p class='cwc-weather-suggestion'>由 GPT-4 Turbo AI 提供的天氣建議暫時停止服務</p>";
        break;
    case 'zh-tw':
        echo "<p class='cwc-weather-suggestion'>由 GPT-4 Turbo AI 提供的天氣建議暫時停止服務</p>";
        break;
    case 'hi':
        echo "<p class='cwc-weather-suggestion'>GPT-4 Turbo AI द्वारा प्रदान की गई मौसम सलाह सेवा अस्थायी रूप से बंद है</p>";
        break;
    case 'uk-ua':
        echo "<p class='cwc-weather-suggestion'>Рекомендації щодо погоди, надані GPT-4 Turbo AI, тимчасово призупинено</p>";
        break;
    case 'en-us':
    default:
        echo "<p class='cwc-weather-suggestion'>Weather advice powered by GPT-4 Turbo AI is temporarily unavailable</p>";
        break;
}