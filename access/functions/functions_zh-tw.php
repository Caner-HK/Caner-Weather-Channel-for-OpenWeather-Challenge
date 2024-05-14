<?php
function getMoonPhaseDescription($moonPhase) {
    $mainPhases = [0, 0.25, 0.5, 0.75, 1];
    foreach ($mainPhases as $phase) {
        if (abs($moonPhase - $phase) < 0.03) {
            $moonPhase = $phase;
            break;
        }
    }
    switch ($moonPhase) {
        case 0:
        case 1:
            return '新月';
        case 0.25:
            return '上弦月';
        case 0.5:
            return '滿月';
        case 0.75:
            return '下弦月';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return '渐盈新月';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return '渐盈凸月';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return '渐亏凸月';
            } else {
                return '渐亏新月';
            }
    }
}

function getMoonPhaseIcon($moonPhase) {
    $mainPhases = [0, 0.25, 0.5, 0.75, 1];
    foreach ($mainPhases as $phase) {
        if (abs($moonPhase - $phase) < 0.03) {
            $moonPhase = $phase;
            break;
        }
    }
    switch ($moonPhase) {
        case 0:
        case 1:
            return 'New_Moon';
        case 0.25:
            return 'First_Quarter_Moon';
        case 0.5:
            return 'Full_Moon';
        case 0.75:
            return 'Last_Quarter_Moon';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return 'Waxing_Crescent_Moon';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return 'Waxing_Gibbous_Moon';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return 'Waning_Gibbous_Moon';
            } else {
                return 'Waning_Crescent_Moon';
            }
    }
}

function getWindDirection($degrees) {
    if (($degrees >= 337.5) || ($degrees < 22.5)) {
        return "北風";
    } elseif ($degrees >= 22.5 && $degrees < 67.5) {
        return "東北風";
    } elseif ($degrees >= 67.5 && $degrees < 112.5) {
        return "東風";
    } elseif ($degrees >= 112.5 && $degrees < 157.5) {
        return "東南風";
    } elseif ($degrees >= 157.5 && $degrees < 202.5) {
        return "南風";
    } elseif ($degrees >= 202.5 && $degrees < 247.5) {
        return "西南風";
    } elseif ($degrees >= 247.5 && $degrees < 292.5) {
        return "西風";
    } elseif ($degrees >= 292.5 && $degrees < 337.5) {
        return "西北風";
    } else {
        return "未知風向";
    }
}

function getUVIndexDescription($uvi) {
    if ($uvi >= 0 && $uvi <= 2) {
        return "低";
    } else if ($uvi > 2 && $uvi <= 5) {
        return "中等";
    } else if ($uvi > 5 && $uvi <= 7) {
        return "高";
    } else if ($uvi > 7 && $uvi <= 10) {
        return "非常高";
    } else {
        return "極端";
    }
}

function getWindSpeedDescription($windSpeed, $units) {
    if ($units == 'imperial') {
        $windSpeed = $windSpeed / 2.23694;
    }
    if ($windSpeed < 0.3) {
        return "無風 (0級)";
    } else if ($windSpeed <= 1.5) {
        return "軟風 (1級)";
    } else if ($windSpeed <= 3.3) {
        return "輕風 (2級)";
    } else if ($windSpeed <= 5.4) {
        return "微風 (3級)";
    } else if ($windSpeed <= 7.9) {
        return "和風 (4級)";
    } else if ($windSpeed <= 10.7) {
        return "勁風 (5級)";
    } else if ($windSpeed <= 13.8) {
        return "強風 (6級)";
    } else if ($windSpeed <= 17.1) {
        return "疾風 (7級)";
    } else if ($windSpeed <= 20.7) {
        return "大風 (8級)";
    } else if ($windSpeed <= 24.4) {
        return "烈風 (9級)";
    } else if ($windSpeed <= 28.4) {
        return "狂風 (10級)";
    } else if ($windSpeed <= 32.6) {
        return "暴風 (11級)";
    } else {
        return "颶風 (12級)";
    }
}


function getVisibilityDescription($visibility) {
    if ($visibility >= 0 && $visibility <= 1000) {
        return "非常低";
    } else if ($visibility > 1000 && $visibility <= 3000) {
        return "低";
    } else if ($visibility > 3000 && $visibility <= 5000) {
        return "中等";
    } else if ($visibility > 5000 && $visibility <= 8000) {
        return "高";
    } else {
        return "非常高";
    }
}

function getHumidityDescription($humidity) {
    if ($humidity >= 0 && $humidity <= 30) {
        return "乾燥";
    } else if ($humidity > 30 && $humidity <= 50) {
        return "舒適";
    } else if ($humidity > 50 && $humidity <= 70) {
        return "適中";
    } else {
        return "潮濕";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds <= 20) {
        return "晴朗";
    } else if ($clouds > 20 && $clouds <= 40) {
        return "少雲";
    } else if ($clouds > 40 && $clouds <= 60) {
        return "半陰";
    } else if ($clouds > 60 && $clouds <= 80) {
        return "多雲";
    } else {
        return "陰天";
    }
}

function getPressureDescription($pressure) {
    if ($pressure < 1000) {
        return "低壓";
    } else if ($pressure >= 1000 && $pressure <= 1020) {
        return "正常";
    } else {
        return "高壓";
    }
}

function getAqiDescription($aqi) {
    if ($aqi == 1) {
        return "優";
    } else if ($aqi == 2) {
        return "良";
    } else if ($aqi == 3) {
        return "輕度汙染";
    } else if ($aqi == 4) {
        return "中度汙染";
    } else {
        return "重度汙染";
    }
}

function getAqiStyle($aqi) {
    switch ($aqi) {
        case 1:
            return ["#F1F8E9", "#8BC34A"];
        case 2:
            return ["#FFFDE7", "#FFEB3B"];
        case 3:
            return ["#FFF3E0", "#FF9800"];
        case 4:
            return ["#FFEBEE", "#F44336"];
        case 5:
            return ["#F3E5F5", "#9C27B0"];
        default:
            return ["#FFFFFF", "#000000"];
    }
}

function getAirPollutionSuggestion($aqi) {
    if ($aqi == 1) {
        return "<strong>空氣品質極佳</strong>，非常適合戶外活動，如健行、騎自行車和園藝工作，你可以充分利用優良的空氣質量，<strong>享受室外时间</strong>。";
    } else if ($aqi == 2) {
        return "<strong>空氣品質整體良好</strong>，大部分人可以進行正常的戶外活動。\n<strong>需要注意：極度敏感的人群應減少長時間的高強度戶外運動。</strong>";
    } else if ($aqi == 3) {
        return "兒童、老年人及心臟病及呼吸系統疾病患者應<strong>减少</strong>長時間或劇烈的戶外運動。\n<strong>建議普通人群在戶外運動時應適度。</strong>";
    } else if ($aqi == 4) {
        return "<strong>所有人群應減少戶外運動</strong>，尤其是兒童、老年人和有心臟病、呼吸系統疾病的人。\n<strong>建議你盡可能在室內進行運動，保持室內空氣流通。</strong>";
    } else if ($aqi == 5) {
        return "<strong>所有人群應避免戶外運動</strong>，尤其是心臟病和呼吸系統疾病患者。\n<strong>建議你盡量待在室內並關閉門窗，使用空氣清淨器，注意健康狀況變化。</strong>";
    } else {
        return "無有效數據";
    }
}

function getPollutantDescription($concentration, $pollutant) {
    $thresholds = [
        'SO2' => [[0, 20], [20, 80], [80, 250], [250, 350], [350, PHP_INT_MAX]],
        'NO2' => [[0, 40], [40, 70], [70, 150], [150, 200], [200, PHP_INT_MAX]],
        'PM10' => [[0, 20], [20, 50], [50, 100], [100, 200], [200, PHP_INT_MAX]],
        'PM2.5' => [[0, 10], [10, 25], [25, 50], [50, 75], [75, PHP_INT_MAX]],
        'O3' => [[0, 60], [60, 100], [100, 140], [140, 180], [180, PHP_INT_MAX]],
        'CO' => [[0, 4400], [4400, 9400], [9400, 12400], [12400, 15400], [15400, PHP_INT_MAX]],
    ];

    $descriptions = ["低", "中", "偏高", "高", "很高"];
    foreach ($thresholds[$pollutant] as $i => $range) {
        if ($concentration >= $range[0] && $concentration < $range[1]) {
            return $descriptions[$i];
        }
    }
    return "未知";
}

function getTemperatureUnit($units) {
    switch ($units) {
        case 'imperial':
            return "°F";
        case 'metric':
            return "°C";
        case 'standard':
            return "K";
        default:
            return "°C";
    }
}

function getWindSpeedUnit($units) {
    switch ($units) {
        case 'imperial':
            return "mph";
        case 'metric':
        case 'standard':
            return "m/s";
        default:
            return "m/s";
    }
}

function getUnitDescription($units) {
    switch ($units) {
        case 'imperial':
            return "英制 | °F, mph, m 和 hPa";
        case 'metric':
            return "公制 | °C, m/s, m 和 hPa";
        case 'standard':
            return "標準 | K, m/s, m 和 hPa";
        default:
            return "公制 | °C, m/s, m 和 hPa";
    }
}

function getMapErrorDescription($countryCode) {
    if ($countryCode == "CN") {
        return "Google Maps 在中國大陸不可用，請轉至設定以選擇 高德地圖";
    } else {
        return "網路連接出現問題，請重新加載或在設定中選擇 AMap";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => '取消',
        'None' => '無',
        'Unknown' => '未知',
        'Standard' => '標準',
        'Minor' => '輕微',
        'Moderate' => '中等',
        'Major' => '重大',
        'Severe' => '嚴重',
        'Extreme' => '極端',
    ];
    return isset($translations[$severity]) ? $translations[$severity] : $severity;
}

function formatUtcOffset($offset) {
    $hours = $offset / 3600;
    $formattedOffset = ($hours >= 0 ? '+' : '') . $hours;
    return "UTC" . $formattedOffset;
}

function convertToLocalTime($timestamp, $timezone) {
    $utcTime = new DateTime(gmdate("Y-m-d\TH:i:s\Z", $timestamp));
    $utcTime->setTimezone(new DateTimeZone($timezone));
    return $utcTime->format('Y-m-d H:i');
}
    
function convertToLocalHM($timestamp, $timezone) {
    $utcTime = new DateTime(gmdate("Y-m-d\TH:i:s\Z", $timestamp));
    $utcTime->setTimezone(new DateTimeZone($timezone));
    return $utcTime->format('H:i');
}
    
function convertToLocalDate($timestamp, $timezone) {
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $date->setTimezone(new DateTimeZone($timezone));
    $formattedDate = $date->format('Y-m-d');
    $weekdays = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
    $weekday = $weekdays[(int)$date->format('w')];
    return $formattedDate . ' ' . $weekday;
}