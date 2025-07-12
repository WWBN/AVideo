<?php


function humanTiming($time, $precision = 0, $useDatabaseTime = true, $addAgo = false)
{
    if (empty($time)) {
        return '';
    }
    $time = secondsIntervalFromNow($time, $useDatabaseTime);

    if ($addAgo) {
        $addAgo = $time - time();
    }

    return secondsToHumanTiming($time, $precision, $addAgo);
}

function humanTimingOrDate($time, $precision = 0, $useDatabaseTime = true, $addAgo = false)
{
    global $advancedCustom;
    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
    }

    if (is_array($time)) {
        $video = $time;
        $time = $video['videoCreation'];
    }

    if (empty($time)) {
        return '';
    }

    $time = _strtotime($time);

    if (empty($advancedCustom->showHumanTimingOnVideoItem)) {
        return date($advancedCustom->showHumanTimingOnVideoItemDateFormat, $time);
    }

    return humanTiming($time, $precision, $useDatabaseTime, $addAgo);
}

/**
 *
 * @param string $time
 * @param string $precision
 * @param string $useDatabaseTime good if you are checking the created time
 * @return string
 */
function humanTimingAgo($time, $precision = 0, $useDatabaseTime = true)
{
    $time = secondsIntervalFromNow($time, $useDatabaseTime);
    if (empty($time)) {
        return __("Now");
    }
    return sprintf(__('%s ago'), secondsToHumanTiming($time, $precision));
}

function humanTimingAfterwards($time, $precision = 0, $useDatabaseTime = true)
{
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }
    $time = secondsIntervalFromNow($time, $useDatabaseTime);
    if (empty($time)) {
        return __("Now");
    } elseif ($time > 0) {
        return sprintf(__('%s Ago'), secondsToHumanTiming($time, $precision));
    }
    return __('Coming in') . ' ' . secondsToHumanTiming($time, $precision);
}

function secondsToHumanTiming($time, $precision = 0, $addAgo = false)
{
    if (empty($time)) {
        return __("Now");
    }
    $time = ($time < 0) ? $time * -1 : $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second',
    ];

    /**
     * For detection purposes only
     */
    __('year');
    __('month');
    __('week');
    __('day');
    __('hour');
    __('minute');
    __('second');
    __('years');
    __('months');
    __('weeks');
    __('days');
    __('hours');
    __('minutes');
    __('seconds');

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }

        $numberOfUnits = floor($time / $unit);
        if ($numberOfUnits > 1) {
            $text = __($text . "s");
        } else {
            $text = __($text);
        }

        if ($precision) {
            $rest = $time % $unit;
            if ($rest) {
                $text .= ' ' . secondsToHumanTiming($rest, $precision - 1);
            }
        }

        $return = $numberOfUnits . ' ' . $text;

        if (!empty($addAgo) && $addAgo < 0) {
            $return = sprintf(__('%s Ago'), $return);
        }

        return $return;
    }
}

function seconds2human($ss)
{
    $s = $ss % 60;
    $m = floor(($ss % 3600) / 60);
    $h = floor(($ss % 86400) / 3600);
    $d = floor(($ss % 2592000) / 86400);
    $M = floor($ss / 2592000);

    $times = [];

    if (!empty($M)) {
        $times[] = "$M " . __('m');
    }
    if (!empty($d)) {
        $times[] = "$d " . __('d');
    }
    if (!empty($h)) {
        $times[] = "$h " . __('h');
    }
    if (!empty($m)) {
        $times[] = "$m " . __('min');
    }
    if (!empty($s)) {
        $times[] = "$s " . __('sec');
    }

    return implode(', ', $times);
}

function secondsIntervalHuman($time, $useDatabaseTime = true)
{
    $dif = secondsIntervalFromNow($time, $useDatabaseTime);
    if ($dif < 0) {
        return humanTimingAfterwards($time, 0, $useDatabaseTime);
    } else {
        return humanTimingAgo($time, 0, $useDatabaseTime);
    }
}

function secondsToTime($seconds, $precision = '%06.3f')
{
    $hours = floor($seconds / 3600);
    $mins = intval(floor($seconds / 60) % 60);
    $secs = ($seconds % 60);   // 1
    $decimal = fmod($seconds, 1); //0.25
    return sprintf("%02d:%02d:{$precision}", $hours, $mins, $secs + $decimal);
}

function timeToSecondsInt($hms)
{
    $a = explode(":", $hms); // split it at the colons
    // minutes are worth 60 seconds. Hours are worth 60 minutes.

    for ($i = 0; $i < 3; $i++) {
        $a[$i] = @intval($a[$i]);
    }

    $seconds = round((+$a[0]) * 60 * 60 + (+$a[1]) * 60 + (+$a[2]));
    return ($seconds);
}

function secondsInterval($time1, $time2)
{
    if (!isset($time1) || !isset($time2)) {
        return 0;
    }
    if (!is_numeric($time1)) {
        $time1 = strtotime($time1);
    }
    if (!is_numeric($time2)) {
        $time2 = strtotime($time2);
    }

    return $time1 - $time2;
}

function isTimeForFuture($time, $useDatabaseTime = true)
{
    $dif = secondsIntervalFromNow($time, $useDatabaseTime);
    if ($dif < 0) {
        return true;
    } else {
        return false;
    }
}

function secondsIntervalFromNow($time, $useDatabaseTimeOrTimezoneString = true)
{
    $timeNow = time();
    //var_dump($time, $useDatabaseTimeOrTimezoneString);
    if (!empty($useDatabaseTimeOrTimezoneString)) {
        if (is_numeric($useDatabaseTimeOrTimezoneString) || is_bool($useDatabaseTimeOrTimezoneString)) {
            //echo $time . '-' . __LINE__ . '=>';
            $timeNow = getDatabaseTime();
        } elseif (is_string($useDatabaseTimeOrTimezoneString)) {
            //echo '-' . __LINE__ . PHP_EOL . PHP_EOL;
            $timeNow = getTimeInTimezone($timeNow, $useDatabaseTimeOrTimezoneString);
        }
    }
    return secondsInterval($timeNow, $time);
}

function parseToFloat($numString)
{
    if (!is_string($numString)) {
        return $numString;
    }
    // Normalize the string by removing spaces
    $numString = str_replace(' ', '', $numString);

    // Decide on the decimal separator based on the position and count of ',' and '.'
    if (substr_count($numString, '.') == 1 && substr_count($numString, ',') == 0) {
        // One dot, no comma, dot is decimal
        $numString = str_replace(',', '', $numString);
    } elseif (substr_count($numString, ',') == 1 && substr_count($numString, '.') == 0) {
        // One comma, no dot, comma is decimal
        $numString = str_replace(',', '.', $numString);
    } else {
        // Complex or ambiguous formatting
        $lastComma = strrpos($numString, ',');
        $lastPeriod = strrpos($numString, '.');

        if ($lastComma > $lastPeriod) {
            // Assume last comma is decimal
            $numString = str_replace('.', '', $numString);
            $numString = substr_replace($numString, '.', $lastComma, 1);
        } else {
            // Assume last period is decimal
            $numString = str_replace(',', '', $numString);
        }
    }

    // Convert to float
    return (float) $numString;
}
