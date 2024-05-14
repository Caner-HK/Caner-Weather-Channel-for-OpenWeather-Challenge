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
            return '新（朔）月';
        case 0.25:
            return '上弦月';
        case 0.5:
            return '满（望）月';
        case 0.75:
            return '下弦月';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return '盈新（朔）月';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return '盈凸月';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return '虧凸月';
            } else {
                return '虧新（朔）月';
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
        return "風向不定";
    }
}

function getUVIndexDescription($uvi) {
    if ($uvi >= 0 && $uvi <= 2) {
        return "低";
    } else if ($uvi > 2 && $uvi <= 5) {
        return "中";
    } else if ($uvi > 5 && $uvi <= 7) {
        return "高";
    } else if ($uvi > 7 && $uvi <= 10) {
        return "甚高";
    } else {
        return "嚴重";
    }
}

function getWindSpeedDescription($windSpeed, $units) {
    if ($units == 'imperial') {
        $windSpeed = $windSpeed / 2.23694; // Convert miles per hour to kilometers per hour
    }
    if ($windSpeed < 2) {
        return "無風 (0級)";
    } else if ($windSpeed <= 12) {
        return "輕微 (1 - 2級)";
    } else if ($windSpeed <= 30) {
        return "和緩 (3 - 4級)";
    } else if ($windSpeed <= 40) {
        return "清勁 (5級)";
    } else if ($windSpeed <= 62) {
        return "強風 (6 - 7級)";
    } else if ($windSpeed <= 87) {
        return "烈風 (8 - 9級)";
    } else if ($windSpeed <= 117) {
        return "暴風 (10 - 11級)";
    } else {
        return "颶風 (12級)";
    }
}

function getVisibilityDescription($visibility) {
    if ($visibility >= 0 && $visibility <= 1000) {
        return "甚低";
    } else if ($visibility > 1000 && $visibility <= 3000) {
        return "低";
    } else if ($visibility > 3000 && $visibility <= 5000) {
        return "中";
    } else if ($visibility > 5000 && $visibility <= 8000) {
        return "高";
    } else {
        return "甚高";
    }
}

function getHumidityDescription($humidity) {
    if ($humidity >= 0 && $humidity < 40) {
        return "非常乾燥";
    } else if ($humidity >= 40 && $humidity < 70) {
        return "乾燥";
    } else if ($humidity >= 70 && $humidity < 95) {
        return "潮濕";
    } else if ($humidity >= 95 && $humidity <= 100) {
        return "非常潮濕";
    } else {
        return "未知濕度";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds < 20) {
        return "天晴";
    } else if ($clouds >= 20 && $clouds < 40) {
        return "多雲";
    } else if ($clouds >= 40 && $clouds < 60) {
        return "天陰";
    } else if ($clouds >= 60 && $clouds <= 80) {
        return "密雲";
    } else {
        return "未知";
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
        return "低";
    } else if ($aqi == 2) {
        return "中";
    } else if ($aqi == 3) {
        return "高";
    } else if ($aqi == 4) {
        return "甚高";
    } else {
        return "嚴重";
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
        return "空氣質素健康指數為<strong>低</strong>，可如常進行室外活動。";
    } else if ($aqi == 2) {
        return "空氣質素健康指數為<strong>中</strong>，大部分人可如常活動。心臟病或呼吸系統疾病患者，一旦出現症狀應考慮<strong>減少<strong>戶外體力消耗。";
    } else if ($aqi == 3) {
        return "空氣質素健康指數為<strong>高</strong>，心臟病或呼吸系統疾病患者應<strong>減少</strong>戶外體力消耗，以及<strong>減少</strong>在戶外逗留的時間，特別在交通繁忙地方。這類人士在參與體育活動前應諮詢醫生意見，在體能活動期間應多作歇息。兒童及長者應<strong>減少</strong>戶外體力消耗，以及<strong>減少</strong>在戶外逗留的時間，特別在交通繁忙地方。";
    } else if ($aqi == 4) {
        return "空氣質素健康指數為<strong>甚高</strong>，心臟病或呼吸系統疾病患者應<strong>盡量減少</strong>戶外體力消耗，以及<strong>盡量減少</strong>在戶外逗留的時間，特別在交通繁忙地方。兒童及長者應<strong>盡量減少</strong>戶外體力消耗，以及<strong>盡量減少</strong>在戶外逗留的時間，特別在交通繁忙地方。從事重體力勞動的戶外工作僱員的僱主應評估戶外工作的風險，並採取適當的預防措施保障僱員的健康，例如<strong>減少</strong>戶外體力消耗，以及<strong>減少</strong>在戶外逗留的時間，特別在交通繁忙地方。一般市民應<strong>減少</strong>戶外體力消耗，以及<strong>減少</strong>在戶外逗留的時間，特別在交通繁忙地方。";
    } else if ($aqi == 5) {
        return "心臟病或呼吸系統疾病患者應<strong>避免</strong>戶外體力消耗，以及<strong>避免</strong>在戶外逗留，特別在交通繁忙地方。兒童及長者應<strong>避免</strong>戶外體力消耗，以及<strong>避免</strong>在戶外逗留，特別在交通繁忙地方。所有戶外工作僱員的僱主應評估戶外工作的風險，並採取適當的預防措施保障僱員的健康，例如</strong>減少</strong>戶外體力消耗，以及</strong>減少</strong>在戶外逗留的時間，特別在交通繁忙地方。一般市民應<strong>盡量減少</strong>戶外體力消耗，以及<strong>盡量減少</strong>在戶外逗留的時間，特別在交通繁忙地方。";
    } else {
        return "沒有有效資料";
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

    $descriptions = ["低", "中", "較高", "高", "甚高"];
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
        return "Google Maps 在中國內地無法使用，請到設定中選擇「高德地圖」";
    } else {
        return "網絡連線已中斷，請重新載入或在設定中選擇「高德地圖」";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => '取消',
        'None' => '沒有資料',
        'Unknown' => '未知',
        'Standard' => '標準',
        'Minor' => '和緩',
        'Medium' => '中等',
        'Relatively High' => '較高',
        'Major' => '甚高',
        'Serious' => '嚴重',
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