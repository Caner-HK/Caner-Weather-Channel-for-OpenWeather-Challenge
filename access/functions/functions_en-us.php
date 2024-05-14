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
            return 'New Moon';
        case 0.25:
            return 'First Quarter Moon';
        case 0.5:
            return 'Full Moon';
        case 0.75:
            return 'Last Quarter Moon';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return 'Waxing Crescent';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return 'Waxing Gibbous';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return 'Waning Gibbous';
            } else {
                return 'Waning Crescent';
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
        return "N";
    } elseif ($degrees >= 11.25 && $degrees < 33.75) {
        return "NNE";
    } elseif ($degrees >= 33.75 && $degrees < 56.25) {
        return "NE";
    } elseif ($degrees >= 56.25 && $degrees < 78.75) {
        return "ENE";
    } elseif ($degrees >= 78.75 && $degrees < 101.25) {
        return "E";
    } elseif ($degrees >= 101.25 && $degrees < 123.75) {
        return "ESE";
    } elseif ($degrees >= 123.75 && $degrees < 146.25) {
        return "SE";
    } elseif ($degrees >= 146.25 && $degrees < 168.75) {
        return "SSE";
    } elseif ($degrees >= 168.75 && $degrees < 191.25) {
        return "S";
    } elseif ($degrees >= 191.25 && $degrees < 213.75) {
        return "SSW";
    } elseif ($degrees >= 213.75 && $degrees < 236.25) {
        return "SW";
    } elseif ($degrees >= 236.25 && $degrees < 258.75) {
        return "WSW";
    } elseif ($degrees >= 258.75 && $degrees < 281.25) {
        return "W";
    } elseif ($degrees >= 281.25 && $degrees < 303.75) {
        return "WNW";
    } elseif ($degrees >= 303.75 && $degrees < 326.25) {
        return "NW";
    } elseif ($degrees >= 326.25 && $degrees < 348.75) {
        return "NNW";
    } else {
        return "Unknown";
    }
}

function getUVIndexDescription($uvi) {
    if ($uvi >= 0 && $uvi <= 2) {
        return "Low";
    } else if ($uvi > 2 && $uvi <= 5) {
        return "Moderate";
    } else if ($uvi > 5 && $uvi <= 7) {
        return "High";
    } else if ($uvi > 7 && $uvi <= 10) {
        return "Very High";
    } else {
        return "Extreme";
    }
}

function getWindSpeedDescription($windSpeed, $units) {
    if ($units == 'imperial') {
        $windSpeed = $windSpeed / 2.23694;
    }
    if ($windSpeed < 0.3) {
        return "Calm (0)";
    } else if ($windSpeed <= 1.5) {
        return "Light Air (1)";
    } else if ($windSpeed <= 3.3) {
        return "Light Breeze (2)";
    } else if ($windSpeed <= 5.4) {
        return "Gentle Breeze (3)";
    } else if ($windSpeed <= 7.9) {
        return "Moderate Breeze (4)";
    } else if ($windSpeed <= 10.7) {
        return "Fresh Breeze (5)";
    } else if ($windSpeed <= 13.8) {
        return "Strong Breeze (6)";
    } else if ($windSpeed <= 17.1) {
        return "Near Gale (7)";
    } else if ($windSpeed <= 20.7) {
        return "Gale (8)";
    } else if ($windSpeed <= 24.4) {
        return "Strong Gale (9)";
    } else if ($windSpeed <= 28.4) {
        return "Storm (10)";
    } else if ($windSpeed <= 32.6) {
        return "Violent Storm (11)";
    } else {
        return "Hurricane (12)";
    }
}

function getVisibilityDescription($visibility) {
    if ($visibility >= 0 && $visibility <= 1000) {
        return "Very Low";
    } else if ($visibility > 1000 && $visibility <= 3000) {
        return "Low";
    } else if ($visibility > 3000 && $visibility <= 5000) {
        return "Moderate";
    } else if ($visibility > 5000 && $visibility <= 8000) {
        return "High";
    } else {
        return "Very High";
    }
}

function getHumidityDescription($humidity) {
    if ($humidity >= 0 && $humidity <= 30) {
        return "Dry";
    } else if ($humidity > 30 && $humidity <= 50) {
        return "Comfortable";
    } else if ($humidity > 50 && $humidity <= 70) {
        return "Moderate";
    } else {
        return "Humid";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds <= 20) {
        return "Clear";
    } else if ($clouds > 20 && $clouds <= 40) {
        return "Few Clouds";
    } else if ($clouds > 40 && $clouds <= 60) {
        return "Partly Cloudy";
    } else if ($clouds > 60 && $clouds <= 80) {
        return "Cloudy";
    } else {
        return "Overcast";
    }
}

function getPressureDescription($pressure) {
    if ($pressure < 1000) {
        return "Low Pressure";
    } else if ($pressure >= 1000 && $pressure <= 1020) {
        return "Normal Pressure";
    } else {
        return "High Pressure";
    }
}

function getAqiDescription($aqi) {
    if ($aqi == 1) {
        return "Excellent";
    } else if ($aqi == 2) {
        return "Good";
    } else if ($aqi == 3) {
        return "Light Pollution";
    } else if ($aqi == 4) {
        return "Moderate Pollution";
    } else {
        return "Heavy Pollution";
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
        return "<strong>The air quality is excellent</strong>, perfect for outdoor activities such as hiking, cycling, and gardening. You can make the most of the good air quality and <strong>enjoy your time outdoors</strong>.";
    } else if ($aqi == 2) {
        return "<strong>The air quality is generally good</strong>, and most people can engage in normal outdoor activities.\n<strong>Note: Extremely sensitive individuals should reduce long-duration, high-intensity outdoor exercise.</strong>";
    } else if ($aqi == 3) {
        return "Children, the elderly, and individuals with heart or respiratory conditions should <strong>reduce</strong> prolonged or intense outdoor activities.\n<strong>It is advised that the general population moderates outdoor activities.</strong>";
    } else if ($aqi == 4) {
        return "<strong>All individuals should reduce outdoor activities</strong>, especially children, the elderly, and those with heart or respiratory conditions.\n<strong>It is advised to engage in indoor activities where possible and ensure good indoor air circulation.</strong>";
    } else if ($aqi == 5) {
        return "<strong>All individuals should avoid outdoor activities</strong>, especially those with heart and respiratory conditions.\n<strong>It is advisable to stay indoors, keep windows and doors closed, use an air purifier, and monitor health conditions closely.</strong>";
    } else {
        return "No valid data";
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

    $descriptions = ["Low", "Moderate", "Slightly High", "High", "Very High"];
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
            return "m/s";
        default:
            return "m/s";
    }
}

function getUnitDescription($units) {
    switch ($units) {
        case 'imperial':
            return "Imperial | °F, mph, m and hPa";
        case 'metric':
            return "Metric | °C, m/s, m and hPa";
        case 'standard':
            return "Standard | K, m/s, m and hPa";
        default:
            return "Metric | °C, m/s, m and hPa";
    }
}

function getMapErrorDescription($countryCode) {
    if ($countryCode == "CN") {
        return "Google Maps is not available in mainland China, please go to settings and select AMap.";
    } else {
        return "There is a problem with the network connection, please reload or go to settings and select AMap.";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => 'Cancel',
        'None' => 'None',
        'Unknown' => 'Unknown',
        'Standard' => 'Standard',
        'Minor' => 'Minor',
        'Moderate' => 'Moderate',
        'Major' => 'Major',
        'Severe' => 'Severe',
        'Extreme' => 'Extreme',
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
    return $utcTime->format('m/d/Y g:i A');
}

function convertToLocalHM($timestamp, $timezone) {
    $utcTime = new DateTime(gmdate("Y-m-d\TH:i:s\Z", $timestamp));
    $utcTime->setTimezone(new DateTimeZone($timezone));
    return $utcTime->format('g:i A');
}

function convertToLocalDate($timestamp, $timezone) {
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $date->setTimezone(new DateTimeZone($timezone));
    $formattedDate = $date->format('m/d/Y'); 
    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $weekday = $weekdays[(int)$date->format('w')];
    return $formattedDate . ' ' . $weekday;
}