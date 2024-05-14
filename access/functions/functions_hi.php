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
            return 'नया चाँद';
        case 0.25:
            return 'पहला तिमाही चाँद';
        case 0.5:
            return 'पूर्ण चाँद';
        case 0.75:
            return 'अंतिम तिमाही चाँद';
        default:
            if ($moonPhase > 0 && $moonPhase < 0.25) {
                return 'बढ़ती हुई शुक्लपक्ष';
            } elseif ($moonPhase > 0.25 && $moonPhase < 0.5) {
                return 'बढ़ती हुई गिबस';
            } elseif ($moonPhase > 0.5 && $moonPhase < 0.75) {
                return 'घटती हुई गिबस';
            } else {
                return 'घटती हुई शुक्लपक्ष';
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
        return "उत्तर";
    } elseif ($degrees >= 11.25 && $degrees < 33.75) {
        return "उत्तर-उत्तर-पूर्व";
    } elseif ($degrees >= 33.75 && $degrees < 56.25) {
        return "उत्तर-पूर्व";
    } elseif ($degrees >= 56.25 && $degrees < 78.75) {
        return "पूर्व-उत्तर-पूर्व";
    } elseif ($degrees >= 78.75 && $degrees < 101.25) {
        return "पूर्व";
    } elseif ($degrees >= 101.25 && $degrees < 123.75) {
        return "पूर्व-दक्षिण-पूर्व";
    } elseif ($degrees >= 123.75 && $degrees < 146.25) {
        return "दक्षिण-पूर्व";
    } elseif ($degrees >= 146.25 && $degrees < 168.75) {
        return "दक्षिण-दक्षिण-पूर्व";
    } elseif ($degrees >= 168.75 && $degrees < 191.25) {
        return "दक्षिण";
    } elseif ($degrees >= 191.25 && $degrees < 213.75) {
        return "दक्षिण-दक्षिण-पश्चिम";
    } elseif ($degrees >= 213.75 && $degrees < 236.25) {
        return "दक्षिण-पश्चिम";
    } elseif ($degrees >= 236.25 && $degrees < 258.75) {
        return "पश्चिम-दक्षिण-पश्चिम";
    } elseif ($degrees >= 258.75 && $degrees < 281.25) {
        return "पश्चिम";
    } elseif ($degrees >= 281.25 && $degrees < 303.75) {
        return "पश्चिम-उत्तर-पश्चिम";
    } elseif ($degrees >= 303.75 && $degrees < 326.25) {
        return "उत्तर-पश्चिम";
    } elseif ($degrees >= 326.25 && $degrees < 348.75) {
        return "उत्तर-उत्तर-पश्चिम";
    } else {
        return "अज्ञात";
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
        return "शांत (0)";
    } else if ($windSpeed <= 1.5) {
        return "हल्की हवा (1)";
    } else if ($windSpeed <= 3.3) {
        return "हल्की बयार (2)";
    } else if ($windSpeed <= 5.4) {
        return "सौम्य बयार (3)";
    } else if ($windSpeed <= 7.9) {
        return "मध्यम बयार (4)";
    } else if ($windSpeed <= 10.7) {
        return "ताज़ा बयार (5)";
    } else if ($windSpeed <= 13.8) {
        return "शक्तिशाली बयार (6)";
    } else if ($windSpeed <= 17.1) {
        return "लगभग तूफ़ान (7)";
    } else if ($windSpeed <= 20.7) {
        return "तूफ़ान (8)";
    } else if ($windSpeed <= 24.4) {
        return "मजबूत तूफ़ान (9)";
    } else if ($windSpeed <= 28.4) {
        return "आंधी (10)";
    } else if ($windSpeed <= 32.6) {
        return "हिंसक आंधी (11)";
    } else {
        return "हरिकेन (12)";
    }
}

function getVisibilityDescription($visibility) {
    if ($visibility >= 0 && $visibility <= 1000) {
        return "बहुत कम";
    } else if ($visibility > 1000 && $visibility <= 3000) {
        return "कम";
    } else if ($visibility > 3000 && $visibility <= 5000) {
        return "मध्यम";
    } else if ($visibility > 5000 && $visibility <= 8000) {
        return "उच्च";
    } else {
        return "बहुत उच्च";
    }
}

function getHumidityDescription($humidity) {
    if ($humidity >= 0 && $humidity <= 30) {
        return "सूखा";
    } else if ($humidity > 30 && $humidity <= 50) {
        return "आरामदायक";
    } else if ($humidity > 50 && $humidity <= 70) {
        return "मध्यम";
    } else {
        return "नम";
    }
}

function getCloudCoverageDescription($clouds) {
    if ($clouds >= 0 && $clouds <= 20) {
        return "स्पष्ट";
    } else if ($clouds > 20 && $clouds <= 40) {
        return "कुछ बादल";
    } else if ($clouds > 40 && $clouds <= 60) {
        return "आंशिक रूप से बादलों वाला";
    } else if ($clouds > 60 && $clouds <= 80) {
        return "बादल छाए हुए";
    } else {
        return "अधिक बादल छाए हुए";
    }
}

function getPressureDescription($pressure) {
    if ($pressure < 1000) {
        return "कम दबाव";
    } else if ($pressure >= 1000 && $pressure <= 1020) {
        return "सामान्य दबाव";
    } else {
        return "उच्च दबाव";
    }
}

function getAqiDescription($aqi) {
    if ($aqi == 1) {
        return "उत्कृष्ट";
    } else if ($aqi == 2) {
        return "अच्छा";
    } else if ($aqi == 3) {
        return "हल्का प्रदूषण";
    } else if ($aqi == 4) {
        return "मध्यम प्रदूषण";
    } else {
        return "भारी प्रदूषण";
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
        return "<strong>हवा की गुणवत्ता उत्कृष्ट है</strong>, जिससे पहाड़ी चढ़ाई, साइकिलिंग, और बागवानी जैसी बाहरी गतिविधियों के लिए यह एकदम सही है। आप अच्छी हवा की गुणवत्ता का पूरा लाभ उठा सकते हैं और <strong>बाहर समय का आनंद ले सकते हैं</strong>।";
    } else if ($aqi == 2) {
        return "<strong>हवा की गुणवत्ता सामान्य रूप से अच्छी है</strong>, और अधिकांश लोग सामान्य बाहरी गतिविधियों में भाग ले सकते हैं।\n<strong>नोट: अत्यधिक संवेदनशील व्यक्तियों को लंबी अवधि की, उच्च तीव्रता की बाहरी व्यायाम को कम करना चाहिए।</strong>";
    } else if ($aqi == 3) {
        return "बच्चों, बुजुर्गों और दिल या श्वसन संबंधी स्थितियों वाले व्यक्तियों को लंबी अवधि की या तीव्र बाहरी गतिविधियों को <strong>कम</strong> करना चाहिए।\n<strong>आम आबादी के लिए यह सलाह दी जाती है कि वे बाहरी गतिविधियों को मध्यम रखें।</strong>";
    } else if ($aqi == 4) {
        return "<strong>सभी व्यक्तियों को बाहरी गतिविधियों को कम करना चाहिए</strong>, विशेष रूप से बच्चों, बुजुर्गों और उन लोगों को जिन्हें दिल या श्वसन संबंधी स्थितियाँ हैं।\n<strong>यह सलाह दी जाती है कि जहां संभव हो इनडोर गतिविधियों में भाग लें और अच्छी इनडोर हवा का प्रवाह सुनिश्चित करें।</strong>";
    } else if ($aqi == 5) {
        return "<strong>सभी व्यक्तियों को बाहरी गतिविधियों से बचना चाहिए</strong>, विशेष रूप से उन लोगों को जिन्हें दिल और श्वसन संबंधी स्थितियाँ हैं।\n<strong>यह सलाह दी जाती है कि घर के अंदर रहें, खिड़कियों और दरवाजों को बंद रखें, एयर प्यूरीफायर का उपयोग करें और स्वास्थ्य स्थितियों पर नज़र रखें।</strong>";
    } else {
        return "कोई वैध डेटा नहीं है";
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

    $descriptions = ["निम्न", "मध्यम", "थोड़ा उच्च", "उच्च", "बहुत उच्च"];
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
            return "Imperial | °F, mph, m और hPa";
        case 'metric':
            return "Metric | °C, m/s, m और hPa";
        case 'standard':
            return "Standard | K, m/s, m और hPa";
        default:
            return "Metric | °C, m/s, m और hPa";
    }
}

function getMapErrorDescription($countryCode) {
    if ($countryCode == "CN") {
        return "मुख्य भूमि चीन में Google Maps उपलब्ध नहीं है, कृपया सेटिंग्स में जाएं और AMap का चयन करें।";
    } else {
        return "नेटवर्क कनेक्शन में समस्या है, कृपया पुनः लोड करें या सेटिंग्स में जाकर AMap का चयन करें।";
    }
}

function getAlertSeverity($severity) {
    $translations = [
        'Cancel' => 'रद्द करें',
        'None' => 'कोई नहीं',
        'Unknown' => 'अज्ञात',
        'Standard' => 'मानक',
        'Minor' => 'मामूली',
        'Moderate' => 'मध्यम',
        'Major' => 'प्रमुख',
        'Severe' => 'गंभीर',
        'Extreme' => 'अत्यधिक',
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
    return $utcTime->format('d/m/Y g:i A');
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
    $formattedDate = $date->format('d/m/Y'); 
    $weekdays = ['रवि', 'सोम', 'मंगल', 'बुध', 'गुरु', 'शुक्र', 'शनि'];
    $weekday = $weekdays[(int)$date->format('w')];
    return $formattedDate . ' ' . $weekday;
}