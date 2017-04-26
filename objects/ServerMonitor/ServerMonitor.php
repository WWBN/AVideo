<?php

class ServerMonitor {

    static function getCpu() {
        $obj = new stdClass();
        $cmd = "cat /proc/cpuinfo";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get CPU ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->title = "";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
            $obj->percent = 0;
            $obj->percent = intval(self::getServerLoad());
            // find model name
            foreach ($output as $value) {
                if (preg_match("/model name.+:(.*)/i", $value, $match)) {
                    $obj->title = $match[1];
                    break;
                }
            }
        }
        return $obj;
    }

    static function getMemory() {
        $obj = new stdClass();
        $cmd = "free";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get Memmory ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->title = "";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
            $obj->memTotalBytes = 0;
            $obj->memUsedBytes = 0;
            $obj->memFreeBytes = 0;
            if (preg_match("/Mem: *([0-9]+) *([0-9]+) *([0-9]+) */i", $output[1], $match)) {
                $obj->memTotalBytes = $match[1]*1024;
                $obj->memUsedBytes = $match[2]*1024;
                $obj->memFreeBytes = $match[3]*1024;
                $onePc = $obj->memTotalBytes / 100;
                $obj->memTotal = self::humanFileSize($obj->memTotalBytes);
                $obj->memUsed = self::humanFileSize($obj->memUsedBytes);
                $obj->memFree = self::humanFileSize($obj->memFreeBytes);
                $obj->percent = intval($obj->memUsedBytes / $onePc);
                $obj->title = "Total: {$obj->memTotal} | Free: {$obj->memFree} | Used: {$obj->memUsed}";
            }
        }
        return $obj;
    }

    static function getDisk() {
        $obj = new stdClass();
        $cmd = "df -h";
        exec($cmd . "  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            $obj->error = "Get Disk ERROR** " . print_r($output, true);
            $obj->command = $cmd;
        } else {
            $obj->percent = 0;
            foreach ($output as $value) {
                if (preg_match("/([0-9]+)% \/$/i", $value, $match)) {
                    $obj->percent = intval($match[1]);
                    break;
                }
            }
            $obj->title = "Usage of {$obj->percent}%";
            $obj->success = 1;
            $obj->output = $output;
            $obj->command = $cmd;
        }
        return $obj;
    }

    static function humanFileSize($size, $unit = "") {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB")
            return number_format($size / (1 << 30), 2) . "GB";
        if ((!$unit && $size >= 1 << 20) || $unit == "MB")
            return number_format($size / (1 << 20), 2) . "MB";
        if ((!$unit && $size >= 1 << 10) || $unit == "KB")
            return number_format($size / (1 << 10), 2) . "KB";
        return number_format($size) . " bytes";
    }

    static private function _getServerLoadLinuxData() {
        if (is_readable("/proc/stat")) {
            $stats = @file_get_contents("/proc/stat");

            if ($stats !== false) {
                // Remove double spaces to make it easier to extract values with explode()
                $stats = preg_replace("/[[:blank:]]+/", " ", $stats);

                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find line for main CPU load
                foreach ($stats as $statLine) {
                    $statLineData = explode(" ", trim($statLine));

                    // Found!
                    if
                    (
                            (count($statLineData) >= 5) &&
                            ($statLineData[0] == "cpu")
                    ) {
                        return array(
                            $statLineData[1],
                            $statLineData[2],
                            $statLineData[3],
                            $statLineData[4],
                        );
                    }
                }
            }
        }

        return null;
    }

    // Returns server load in percent (just number, without percent sign)
    static function getServerLoad() {
        $load = null;

        if (stristr(PHP_OS, "win")) {
            $cmd = "wmic cpu get loadpercentage /all";
            @exec($cmd, $output);

            if ($output) {
                foreach ($output as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $load = $line;
                        break;
                    }
                }
            }
        } else {
            if (is_readable("/proc/stat")) {
                // Collect 2 samples - each with 1 second period
                // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
                $statData1 = self::_getServerLoadLinuxData();
                sleep(1);
                $statData2 = self::_getServerLoadLinuxData();

                if
                (
                        (!is_null($statData1)) &&
                        (!is_null($statData2))
                ) {
                    // Get difference
                    $statData2[0] -= $statData1[0];
                    $statData2[1] -= $statData1[1];
                    $statData2[2] -= $statData1[2];
                    $statData2[3] -= $statData1[3];

                    // Sum up the 4 values for User, Nice, System and Idle and calculate
                    // the percentage of idle time (which is part of the 4 values!)
                    $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

                    // Invert percentage to get CPU time, not idle time
                    $load = 100 - ($statData2[3] * 100 / $cpuTime);
                }
            }
        }

        return $load;
    }

}
