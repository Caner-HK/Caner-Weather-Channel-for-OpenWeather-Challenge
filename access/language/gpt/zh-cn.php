<?php
return [
    'get_err' => '接收天气数据失败，无法生成建议',
    'location' => '位置',
    'current_weather' => '当前天气情况',
    'temp' => '温度',
    'feelslike' => '体感温度',
    'pressure' => '气压',
    'humidity' => '湿度',
    'dew_point' => '露点',
    'clouds' => '云量',
    'visibility' => '能见度',
    'wind_speed' => '风速',
    'wind_deg' => '风向',
    'forecast_weather' => '未来几小时的天气预报',
    'aqi' => '当前AQI',
    'aqi_max' => '(共5级)',
    'alert' => '天气预警',
    'advice_prompt' => '请根据提供的天气数据, 考虑其对日常生活的影响, 并提供相应的穿衣与出行建议。在制定建议时, 请避免重复提及已提供的具体天气信息, 但可以引用空气质量和天气预警信息, 避免大量使用引导性的开头文字, 以建议为主要内容。可以以这样的提示语和格式：<strong>穿衣建议：</strong> <strong>出行建议：</strong> <strong>特别提示：</strong>（特别建议非必须项目, 但在恶劣天气如天气预警、低温、紫外线等级高、能见度低、风速大、强降水和恶劣空气质量时要特别提醒）在每个建议类型间, 请使用两个HTML换行标签 (<br><br>) 进行分隔, 以便清晰表述。',
    'resp_lang' => '使用简体中文回复提示语建议。',
    'unavailable' => '<p class=\'cwc-weather-suggestion\'>暂时无法提供建议, 请刷新页面或稍候尝试或 <a class=\'cwc-link\' href=\'mailto:support@caner.hk\'>联系 Caner 支持人员</a></p>',
    'resp_err' => '<p class=\'cwc-weather-suggestion\'>响应数据不完整 <a class=\'cwc-link\' href=\'mailto:support@caner.hk\'>联系 Caner 支持人员</a> 以反馈此问题</p>',
];