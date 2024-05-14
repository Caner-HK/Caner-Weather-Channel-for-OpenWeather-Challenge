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
            return 'Новий Місяць';
        case 0.25:
            return 'Перша чверть';
        case 0.5:
            return 'Повний Місяць';
        case 0.75:
            return 'Остання чверть';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return 'Зростаючий серп';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return 'Зростаючий гібб';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return 'Спадаючий гібб';
            } else {
                return 'Спадаючий серп';
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
    if ($degrees >= 348.75 || $degrees < 11.25) {
        return "Пн";
    } elseif ($degrees >= 11.25 && $degrees < 33.75) {
        return "ПнПнСх";
    } elseif ($degrees >= 33.75 && $degrees < 56.25) {
        return "ПнСх";
    } elseif ($degrees >= 56.25 && $degrees < 78.75) {
        return "СхПнСх";
    } elseif ($degrees >= 78.75 && $degrees < 101.25) {
        return "Сх";
    } elseif ($degrees >= 101.25 && $degrees < 123.75) {
        return "СхПдСх";
    } elseif ($degrees >= 123.75 && $degrees < 146.25) {
        return "ПдСх";
    } elseif ($degrees >= 146.25 && $degrees < 168.75) {
        return "ПдПдСх";
    } elseif ($degrees >= 168.75 && $degrees < 191.25) {
        return "Пд";
    } elseif ($degrees >= 191.25 && $degrees < 213.75) {
        return "ПдПдЗх";
    } elseif ($degrees >= 213.75 && $degrees < 236.25) {
        return "ПдЗх";
    } elseif ($degrees >= 236.25 && $degrees < 258.75) {
        return "ЗхПдЗх";
    } elseif ($degrees >= 258.75 && $degrees < 281.25) {
        return "Зх";
    } elseif ($degrees >= 281.25 && $degrees < 303.75) {
        return "ЗхПнЗх";
    } elseif ($degrees >= 303.75 && $degrees < 326.25) {
        return "ПнЗх";
    } elseif ($degrees >= 326.25 && $degrees < 348.75) {
        return "ПнПнЗх";
    } else {
        return "Невідомо";
    }
}

function getUVIndexDescription($uvi) {
    if ($uvi >= 0 && $uvi <= 2) {
        return "Низький";
    } else if ($uvi > 2 && $uvi <= 5) {
        return "Помірний";
    } else if ($uvi > 5 && $uvi <= 7) {
        return "Високий";
    } else if ($uvi > 7 && $uvi <= 10) {
        return "Дуже високий";
    } else {
        return "Екстремальний";
    }
}

function getWindSpeedDescription($windSpeed, $units) {
    if ($units == 'imperial') {
        $windSpeed = $windSpeed / 2.23694;
    }
    if ($windSpeed < 0.3) {
        return "Спокій (0)";
    } else if ($windSpeed <= 1.5) {
        return "Слабкий вітер (1)";
    } else if ($windSpeed <= 3.3) {
        return "Легкий бриз (2)";
    } else if ($windSpeed <= 5.4) {
        return "Ніжний бриз (3)";
    } else if ($windSpeed <= 7.9) {
        return "Помірний бриз (4)";
    } else if ($windSpeed <= 10.7) {
        return "Свіжий бриз (5)";
    } else if ($windSpeed <= 13.8) {
        return "Сильний бриз (6)";
    } else if ($windSpeed <= 17.1) {
        return "Майже шторм (7)";
    } else if ($windSpeed <= 20.7) {
        return "Шторм (8)";
    } else if ($windSpeed <= 24.4) {
        return "Сильний шторм (9)";
    } else if ($windSpeed <= 28.4) {
        return "Буря (10)";
    } else if ($windSpeed <= 32.6) {
        return "Сильна буря (11)";
    } else {
        return "Ураган (12)";
    }
}

function getVisibilityDescription($visibility) {
    if ($visibility >= 0 && $visibility <= 1000) {
        return "Дуже низька";
    } else if ($visibility > 1000 && $visibility <= 3000) {
        return "Низька";
    } else if ($visibility > 3000 && $visibility <= 5000) {
        return "Помірна";
    } else if ($visibility > 5000 && $visibility <= 8000) {
        return "Висока";
    } else {
        return "Дуже висока";
    }
}

function getHumidityDescription($humidity) {
    if ($humidity >= 0 && $humidity <= 30) {
        return "Сухо";
    } else if ($humidity > 30 && $humidity <= 50) {
        return "Комфортно";
    } else if ($humidity > 50 && $humidity <= 70) {
        return "Помірно";
    } else {
        return "Волого";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds <= 20) {
        return "Ясно";
    } else if ($clouds > 20 && $clouds <= 40) {
        return "Мало хмар";
    } else if ($clouds > 40 && $clouds <= 60) {
        return "Частково хмарно";
    } else if ($clouds > 60 && $clouds <= 80) {
        return "Хмарно";
    } else {
        return "Похмуро";
    }
}

function getPressureDescription($pressure) {
    if ($pressure < 1000) {
        return "Низький тиск";
    } else if ($pressure >= 1000 && $pressure <= 1020) {
        return "Нормальний тиск";
    } else {
        return "Високий тиск";
    }
}

function getAqiDescription($aqi) {
    if ($aqi == 1) {
        return "Відмінна";
    } else if ($aqi == 2) {
        return "Добра";
    } else if ($aqi == 3) {
        return "Легке забруднення";
    } else if ($aqi == 4) {
        return "Помірне забруднення";
    } else {
        return "Сильне забруднення";
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
        return "<strong>Якість повітря відмінна</strong>, ідеально підходить для занять на відкритому повітрі, таких як походи, велосипедні прогулянки та садівництво. Скористайтеся можливістю насолоджуватися часом на свіжому повітрі.";
    } else if ($aqi == 2) {
        return "<strong>Якість повітря загалом добра</strong>, і більшість людей можуть займатися звичайними заняттями на відкритому повітрі.\n<strong>Примітка: Особливо чутливі особи повинні скоротити тривалі та інтенсивні вправи на свіжому повітрі.</strong>";
    } else if ($aqi == 3) {
        return "Дітям, літнім людям та особам з захворюваннями серця чи дихальної системи слід <strong>скоротити</strong> тривалі або інтенсивні заняття на відкритому повітрі.\n<strong>Рекомендується помірність занять на відкритому повітрі для всіх груп населення.</strong>";
    } else if ($aqi == 4) {
        return "<strong>Всім особам слід зменшити заняття на відкритому повітрі</strong>, особливо дітям, літнім людям та тим, у кого є захворювання серця чи дихальної системи.\n<strong>Рекомендується проводити час у приміщенні, де це можливо, і забезпечити гарну циркуляцію повітря всередині.</strong>";
    } else if ($aqi == 5) {
        return "<strong>Всім особам слід уникати занять на відкритому повітрі</strong>, особливо тим, хто страждає на захворювання серця та дихальної системи.\n<strong>Рекомендується залишатися вдома, тримати вікна та двері зачиненими, використовувати очищувач повітря та уважно стежити за станом здоров'я.</strong>";
    } else {
        return "Немає дійсних даних";
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

    $descriptions = ["Низький", "Помірний", "Трохи високий", "Високий", "Дуже високий"];
    foreach ($thresholds[$pollutant] as $i => $range) {
        if ($concentration >= $range[0] && $concentration < $range[1]) {
            return $descriptions[$i];
        }
    }
    return "Unknown";
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
            return "м/с";
        default:
            return "м/с";
    }
}

function getUnitDescription($units) {
    switch ($units) {
        case 'imperial':
            return "Імперіальна система | °F, миль/год, м і гПа";
        case 'metric':
            return "Метрична система | °C, м/с, м і гПа";
        case 'standard':
            return "Стандартна система | K, м/с, м і гПа";
        default:
            return "Метрична система | °C, м/с, м і гПа";
    }
}

function getMapErrorDescription($countryCode) {
    if ($countryCode == "CN") {
        return "Google Maps недоступні в континентальному Китаї, будь ласка, перейдіть в налаштування і виберіть AMap.";
    } else {
        return "Проблема з мережевим з\'єднанням, будь ласка, перезавантажте сторінку або перейдіть в налаштування і виберіть AMap.";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => 'Скасувати',
        'None' => 'Відсутня',
        'Unknown' => 'Невідомо',
        'Standard' => 'Стандарт',
        'Minor' => 'Незначний',
        'Moderate' => 'Помірний',
        'Major' => 'Значний',
        'Severe' => 'Тяжкий',
        'Extreme' => 'Екстремальний',
    ];
    return isset($translations[$severity]) ? $translations[$severity] : $severity;
}

function formatUtcOffset($offset) {
    $hours = $offset / 3600;
    $formattedOffset = sprintf("%+d", $hours);
    return "UTC" . $formattedOffset;
}

function convertToLocalTime($timestamp, $timezone) {
    $utcTime = new DateTime(gmdate("Y-m-d\TH:i:s\Z", $timestamp));
    $utcTime->setTimezone(new DateTimeZone($timezone));
    return $utcTime->format('d/m/Y H:i');
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
    $formattedDate = $date->format('d/m/Y');
    $weekdays = ['Нед', 'Пон', 'Вів', 'Сер', 'Чет', 'Птн', 'Суб'];
    $weekday = $weekdays[(int)$date->format('w')];
    return $formattedDate . ' ' . $weekday;
}