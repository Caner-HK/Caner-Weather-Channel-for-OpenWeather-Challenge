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
            return '满月';
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
        return "北风";
    } elseif ($degrees >= 22.5 && $degrees < 67.5) {
        return "东北风";
    } elseif ($degrees >= 67.5 && $degrees < 112.5) {
        return "东风";
    } elseif ($degrees >= 112.5 && $degrees < 157.5) {
        return "东南风";
    } elseif ($degrees >= 157.5 && $degrees < 202.5) {
        return "南风";
    } elseif ($degrees >= 202.5 && $degrees < 247.5) {
        return "西南风";
    } elseif ($degrees >= 247.5 && $degrees < 292.5) {
        return "西风";
    } elseif ($degrees >= 292.5 && $degrees < 337.5) {
        return "西北风";
    } else {
        return "未知风向";
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
        return "极端";
    }
}

function getWindSpeedDescription($windSpeed, $units) {
    if ($units == 'imperial') {
        $windSpeed = $windSpeed / 2.23694;
    }
    if ($windSpeed < 0.3) {
        return "无风 (0级)";
    } else if ($windSpeed <= 1.5) {
        return "软风 (1级)";
    } else if ($windSpeed <= 3.3) {
        return "轻风 (2级)";
    } else if ($windSpeed <= 5.4) {
        return "微风 (3级)";
    } else if ($windSpeed <= 7.9) {
        return "和风 (4级)";
    } else if ($windSpeed <= 10.7) {
        return "劲风 (5级)";
    } else if ($windSpeed <= 13.8) {
        return "强风 (6级)";
    } else if ($windSpeed <= 17.1) {
        return "疾风 (7级)";
    } else if ($windSpeed <= 20.7) {
        return "大风 (8级)";
    } else if ($windSpeed <= 24.4) {
        return "烈风 (9级)";
    } else if ($windSpeed <= 28.4) {
        return "狂风 (10级)";
    } else if ($windSpeed <= 32.6) {
        return "暴风 (11级)";
    } else {
        return "飓风 (12级)";
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
        return "干燥";
    } else if ($humidity > 30 && $humidity <= 50) {
        return "舒适";
    } else if ($humidity > 50 && $humidity <= 70) {
        return "适中";
    } else {
        return "潮湿";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds <= 20) {
        return "晴朗";
    } else if ($clouds > 20 && $clouds <= 40) {
        return "少云";
    } else if ($clouds > 40 && $clouds <= 60) {
        return "半阴";
    } else if ($clouds > 60 && $clouds <= 80) {
        return "多云";
    } else {
        return "阴天";
    }
}

function getPressureDescription($pressure) {
    if ($pressure < 1000) {
        return "低压";
    } else if ($pressure >= 1000 && $pressure <= 1020) {
        return "正常";
    } else {
        return "高压";
    }
}

function getAqiDescription($aqi) {
    if ($aqi == 1) {
        return "优";
    } else if ($aqi == 2) {
        return "良";
    } else if ($aqi == 3) {
        return "轻度污染";
    } else if ($aqi == 4) {
        return "中度污染";
    } else {
        return "重度污染";
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
        return "<strong>空气质量极佳</strong>，非常适合户外活动，如徒步、骑自行车和园艺工作，你可以充分利用优良的空气质量，<strong>享受室外时间</strong>。";
    } else if ($aqi == 2) {
        return "<strong>空气质量总体良好</strong>，大部分人可以进行正常的户外活动。\n<strong>需要注意：极度敏感的人群应减少长时间的高强度户外锻炼。</strong>";
    } else if ($aqi == 3) {
        return "儿童、老年人及心脏病和呼吸系统疾病患者应<strong>减少</strong>长时间或剧烈的户外运动。\n<strong>建议普通人群在户外运动时应适度。</strong>";
    } else if ($aqi == 4) {
        return "<strong>所有人群应减少户外运动</strong>，尤其是儿童、老年人和有心脏病、呼吸系统疾病的人。\n<strong>建议你尽可能在室内进行运动，保持室内空气流通。</strong>";
    } else if ($aqi == 5) {
        return "<strong>所有人群应避免户外运动</strong>，尤其是心脏病和呼吸系统疾病患者。\n<strong>建议你尽量待在室内并关闭门窗，使用空气净化器，关注健康状况变化。</strong>";
    } else {
        return "无有效数据";
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
            return "标准 | K, m/s, m 和 hPa";
        default:
            return "公制 | °C, m/s, m 和 hPa";
    }
}

function getMapErrorDescription($countryCode) {
    if ($countryCode == "CN") {
        return "Google Maps 在中国大陆不可用，请转至设置以选择 高德地图";
    } else {
        return "网络连接出现问题，请重新加载或在设置中选择 AMap";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => '取消',
        'None' => '无',
        'Unknown' => '未知',
        'Standard' => '标准',
        'Minor' => '轻微',
        'Moderate' => '中等',
        'Major' => '重大',
        'Severe' => '严重',
        'Extreme' => '极端',
    ];
    return isset($translations[$severity]) ? $translations[$severity] : $severity;
}